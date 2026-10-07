<?php

namespace app\common\agent\services;

use app\admin\model\Config as ConfigModel;
use app\common\agent\{AgentContext, AgentToolExecutor, AgentToolRegistry};
use badou\Server;
use badou\TableManager;
use think\facade\Cache;

/**
 * 插件管理业务。
 * 控制器 app\admin\controller\Module 只是解析 HTTP 请求再调用 badou\Server，
 * 这里直接复用同一批 Server / Config / TableManager 方法，供 Agent 工具调用。
 */
class ModuleAgentService
{
    /** 后台功能与 AI 工具分别来自可见菜单和公共注册中心，不推测代码目录中的能力。 */
    public function capabilities(AgentContext $context, string $name): array
    {
        $name = strtolower(trim($name));
        if (!preg_match('/^[a-z][a-z0-9_]*$/D', $name)) throw new \DomainException('插件名称格式无效');
        $module = Server::getInstalldModuleList()[$name] ?? null;
        if ($module === null) throw new \DomainException('该插件尚未安装');
        $enabled = (int)($module['state'] ?? 0) === 1;
        $menus = $enabled ? (new MenuAgentService())->moduleNavigation($context->auth->getMenus($context->adminId), $name) : [];
        $definitions = (new AgentToolRegistry())->available($context, new AgentToolExecutor(), 'admin', [$name]);
        $tools = array_map(static fn ($tool) => [
            'name' => $tool['name'], 'label' => $tool['label'], 'description' => $tool['description'],
            'requires_approval' => $tool['approval'] !== false,
        ], $definitions);
        return [
            'module' => ['name' => $name, 'title' => (string)($module['title'] ?? $name), 'enabled' => $enabled],
            'backend_features' => $menus,
            'agent_tools' => $tools,
            'format' => '分别说明当前账号可见的后台功能和已接入 Agent 的操作，后台功能链接必须写成 [title](url)，使用返回的菜单标题，不用权限节点或路由名作为链接文字。'
                . '菜单存在不代表 Agent 已能执行；工具列表为空表示当前账号暂无可用的该插件 AI 工具。根据这些结果直接回答，不要继续查找工具。',
        ];
    }

    public function installedList(string $keyword = '', string $type = ''): array
    {
        Server::clearInstalldModuleListCache();
        $modules = Server::getInstalldModuleList();
        $keyword = trim($keyword);
        $type = trim($type);
        $rows = [];
        foreach ($modules as $name => $module) {
            if ($type !== '' && $type !== 'all' && (string)($module['type'] ?? 'module') !== $type) {
                continue;
            }
            if ($keyword !== '') {
                $haystack = mb_strtolower(implode(' ', [$name, $module['title'] ?? '', $module['intro'] ?? '', $module['author'] ?? '']));
                if (mb_stripos($haystack, $keyword) === false) {
                    continue;
                }
            }
            $rows[] = [
                'name' => (string)$name,
                'title' => (string)($module['title'] ?? $name),
                'version' => (string)($module['version'] ?? ''),
                'state' => (int)($module['state'] ?? 0),
                'state_text' => ((int)($module['state'] ?? 0) === 1 ? '已启用' : '已禁用'),
                'type' => (string)($module['type'] ?? 'module'),
                'author' => (string)($module['author'] ?? ''),
                'intro' => (string)($module['intro'] ?? ''),
            ];
        }
        return ['total' => count($rows), 'modules' => $rows];
    }

    private const CATALOG_KEY = 'agent_module_catalog_v2';
    private const CATALOG_TTL = 259200; // 3 天
    private const CATALOG_TYPES = ['module', 'template', 'vip_template'];

    /** 搜索插件市场；数据来自本地缓存，命中缓存时不再请求远端。 */
    public function store(string $keyword = '', string $type = 'all', int $limit = 20, bool $refresh = false): array
    {
        $limit = max(1, min(200, $limit));
        $keyword = trim($keyword);
        $all = $this->catalog($refresh);
        $rows = [];
        foreach ($all as $item) {
            if (in_array($type, self::CATALOG_TYPES, true) && $item['type'] !== $type) {
                continue;
            }
            if ($keyword !== '') {
                $haystack = mb_strtolower($item['name'] . ' ' . $item['title'] . ' ' . $item['intro']);
                if (mb_stripos($haystack, $keyword) === false) {
                    continue;
                }
            }
            $rows[] = $item;
            if (count($rows) >= $limit) {
                break;
            }
        }
        return [
            'keyword' => $keyword,
            'type' => $type,
            'cached_at' => (int)Cache::get(self::CATALOG_KEY . ':time', 0),
            'total' => count($all),
            'returned' => count($rows),
            'plugins' => $rows,
        ];
    }

    /** 全量插件目录（带描述），优先读缓存，refresh=true 强制刷新。 */
    public function catalog(bool $refresh = false): array
    {
        $cached = Cache::get(self::CATALOG_KEY);
        if (!$refresh && is_array($cached) && $cached !== []) {
            return $this->withInstalled($cached);
        }
        $items = $this->fetchCatalog();
        if ($items !== []) {
            Cache::set(self::CATALOG_KEY, $items, self::CATALOG_TTL);
            Cache::set(self::CATALOG_KEY . ':time', time(), self::CATALOG_TTL);
        } elseif (is_array($cached) && $cached !== []) {
            return $this->withInstalled($cached); // 拉取失败退回旧缓存
        }
        return $this->withInstalled($items);
    }

    private function fetchCatalog(): array
    {
        $installed = $this->installedNames();
        $user = Cache::get('bd_u') ?: [];
        $base = [
            'page' => 1,
            'limit' => 200,
            'keyword' => '',
            'bdversion' => (string)config('badouadmin.version'),
            'installed_names' => implode(',', $installed),
            'uid' => (int)($user['uid'] ?? 0),
            'token' => (string)($user['token'] ?? ''),
        ];
        $items = [];
        $errors = [];
        foreach (self::CATALOG_TYPES as $type) {
            try {
                $res = Server::modules($base + ['type' => $type]);
            } catch (\Throwable $e) {
                $errors[] = $type . ':' . $e->getMessage();
                continue;
            }
            if ((int)($res['code'] ?? 0) !== 1) {
                $errors[] = $type . ':' . (string)($res['msg'] ?? '未知错误');
                continue;
            }
            foreach ((array)($res['data'] ?? []) as $item) {
                if (!is_array($item) || empty($item['name'])) {
                    continue;
                }
                $items[$item['name']] = [
                    'name' => (string)$item['name'],
                    'title' => (string)($item['title'] ?? ''),
                    'version' => (string)($item['version'] ?? ''),
                    'price' => (string)($item['price'] ?? ''),
                    'price_text' => ((float)($item['price'] ?? 0) <= 0 ? '免费' : '付费'),
                    'author' => (string)($item['author'] ?? ''),
                    'type' => (string)($item['type'] ?? $type),
                    'type_text' => (string)($item['type_text'] ?? ''),
                    'intro' => (string)($item['intro'] ?? ''),
                ];
            }
        }
        if ($items === [] && $errors !== []) {
            throw new \DomainException('插件市场暂时不可用：' . implode('；', $errors));
        }
        return array_values($items);
    }

    private function withInstalled(array $items): array
    {
        $installed = $this->installedNames();
        return array_map(static function (array $item) use ($installed): array {
            $item['installed'] = in_array($item['name'], $installed, true);
            return $item;
        }, $items);
    }

    /** 实时取已安装插件名（先清 Server 的列表缓存，保证安装/卸载后立刻准确）。 */
    private function installedNames(): array
    {
        Server::clearInstalldModuleListCache();
        return array_keys(Server::getInstalldModuleList());
    }

    public function info(string $name): array
    {
        $name = $this->validName($name);
        $local = Server::getModuleInfo($name);
        if ($local) {
            $result = ['module' => $local, 'state' => (int)($local['state'] ?? 0), 'installed' => true, 'source' => 'local'];
            // 远程详情可选，需要官方账号，取不到不影响本地信息
            try {
                $remote = Server::moduleInfo($name, $this->extend($name));
                if ((int)($remote['code'] ?? 0) === 1) {
                    $result['remote'] = $remote['data'] ?? [];
                }
            } catch (\Throwable $e) {
                $result['remote_error'] = $e->getMessage();
            }
            return $result;
        }
        // 未安装：从市场缓存里找，避免把市场插件误报成“不存在”
        foreach ($this->catalog() as $item) {
            if ($item['name'] === $name) {
                return ['module' => $item, 'state' => 0, 'installed' => (bool)($item['installed'] ?? false), 'source' => 'market'];
            }
        }
        throw new \DomainException('插件不存在：' . $name);
    }

    public function tables(string $name): array
    {
        $name = $this->validName($name);
        return ['name' => $name, 'tables' => array_values(Server::getModuleTables($name))];
    }

    public function configGet(string $name): array
    {
        $items = ConfigModel::where('group', $name)->order('weigh desc,id asc')->select()->toArray();
        return [
            'group' => $name,
            'items' => array_map(static fn ($item) => [
                'name' => (string)$item['name'],
                'title' => (string)$item['title'],
                'type' => (string)$item['type'],
                'value' => $item['value'],
            ], $items),
        ];
    }

    public function configSet(string $name, string $key, string $value, bool $confirm): array
    {
        $item = ConfigModel::where('group', $name)->where('name', $key)->find();
        if (!$item) {
            throw new \DomainException('配置项不存在：' . $key);
        }
        if (!$confirm) {
            return [
                'requires_confirmation' => true,
                'group' => $name,
                'name' => $key,
                'current' => $item->value,
                'message' => '确认后传 confirm=true 写入新值。',
            ];
        }
        $item->save(['value' => $value]);
        return ['updated' => true, 'group' => $name, 'name' => $key, 'value' => $item->value];
    }

    public function install(string $name, bool $force, bool $confirm): array
    {
        $name = $this->validName($name);
        if (isset(Server::getInstalldModuleList()[$name])) {
            throw new \DomainException('插件已安装：' . $name);
        }
        if (!$confirm) {
            return ['requires_confirmation' => true, 'message' => "即将安装插件 {$name}，确认后传 confirm=true。"];
        }
        $info = Server::install($name, $force, $this->extend($name, $this->remoteVersion($name)));
        return ['installed' => true, 'name' => $name, 'module' => $info];
    }

    public function uninstall(string $name, bool $force, bool $dropTables, bool $confirm): array
    {
        $name = $this->validName($name);
        if (!isset(Server::getInstalldModuleList()[$name])) {
            throw new \DomainException('插件未安装：' . $name);
        }
        if (!$confirm) {
            return [
                'requires_confirmation' => true,
                'message' => "即将卸载插件 {$name}" . ($dropTables ? '（并删除其数据表，不可恢复）' : '') . '，确认后传 confirm=true。',
            ];
        }
        $tables = [];
        if ($dropTables) {
            $prefix = (string)(Server::getDbConfig()['prefix'] ?? '');
            foreach (Server::getModuleTables($name) as $table) {
                if ($prefix !== '' && str_starts_with((string)$table, $prefix . $name)) {
                    $tables[] = $table;
                }
            }
        }
        Server::uninstall($name, $force);
        if ($tables) {
            $db = \think\facade\Db::connect();
            foreach ($tables as $table) {
                $db->execute('DROP TABLE IF EXISTS `' . $table . '`');
            }
        }
        return ['uninstalled' => true, 'name' => $name, 'dropped_tables' => $tables];
    }

    public function toggle(string $name, string $action, bool $force, bool $confirm): array
    {
        $name = $this->validName($name);
        $action = $action === 'enable' ? 'enable' : 'disable';
        if (!isset(Server::getInstalldModuleList()[$name])) {
            throw new \DomainException('插件未安装：' . $name);
        }
        if (!$confirm) {
            return [
                'requires_confirmation' => true,
                'message' => '即将' . ($action === 'enable' ? '启用' : '禁用') . "插件 {$name}，确认后传 confirm=true。",
            ];
        }
        if ($action === 'enable') {
            // enable 需要官方账号凭据校验
            Server::enable($name, $force, $this->extend($name));
        } else {
            // disable 不需要官方凭据
            Server::disable($name, $force);
        }
        Server::clearInstalldModuleListCache();
        $info = Server::getInstalldModuleList()[$name] ?? [];
        $state = (int)($info['state'] ?? 0);
        return [
            'toggled' => true,
            'name' => $name,
            'action' => $action,
            'state' => $state,
            'state_text' => $state === 1 ? '已启用' : '已禁用',
        ];
    }

    public function upgrade(string $name, string $version, bool $confirm): array
    {
        $name = $this->validName($name);
        $current = (string)(Server::getModuleInfo($name)['version'] ?? '');
        if ($version === '') {
            $version = $this->remoteVersion($name);
        }
        if ($version === '' || $version === $current) {
            throw new \DomainException('没有可升级的版本');
        }
        if (!$confirm) {
            return ['requires_confirmation' => true, 'current' => $current, 'target' => $version, 'message' => '确认后传 confirm=true 执行升级。'];
        }
        $info = Server::upgrade($name, $this->extend($name, $version));
        return ['upgraded' => true, 'name' => $name, 'from' => $current, 'to' => $version, 'module' => $info];
    }

    public function importTestdata(string $name, bool $confirm): array
    {
        $name = $this->validName($name);
        if (!is_file(Server::getTestdataFile($name))) {
            throw new \DomainException('该插件没有测试数据文件：' . $name);
        }
        if (!$confirm) {
            return ['requires_confirmation' => true, 'message' => "将先备份数据库再导入 {$name} 的 testdata.sql，确认后传 confirm=true。"];
        }
        $backupdir = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR;
        TableManager::backup('all', 1, $backupdir);
        Server::importsql($name, 'testdata.sql');
        return ['imported' => true, 'name' => $name, 'backup_dir' => $backupdir];
    }

    /** 官方账号凭据（登录官方时由 module/saveUserInfo 写入缓存）。 */
    private function credentials(): array
    {
        $user = Cache::get('bd_u') ?: [];
        $uid = (int)($user['uid'] ?? 0);
        $token = (string)($user['token'] ?? '');
        if ($uid <= 0 || $token === '') {
            throw new \DomainException('当前站点未登录badoucms官方账号，无法执行该插件操作，请先到「插件管理」登录官方账号');
        }
        return [$uid, $token];
    }

    private function extend(string $name, string $version = ''): array
    {
        [$uid, $token] = $this->credentials();
        return [
            'uid' => $uid,
            'token' => $token,
            'bdversion' => (string)config('badouadmin.version'),
            'version' => $version !== '' ? $version : (string)(Server::getModuleInfo($name)['version'] ?? ''),
        ];
    }

    private function remoteVersion(string $name): string
    {
        try {
            $remote = Server::moduleInfo($name, $this->extend($name));
            return (string)($remote['data']['version'] ?? '');
        } catch (\Throwable) {
            return '';
        }
    }

    private function validName(string $name): string
    {
        $name = trim($name);
        if ($name === '' || !preg_match('/^[a-zA-Z0-9]+$/', $name)) {
            throw new \DomainException('插件名不合法（只能是字母和数字）');
        }
        return $name;
    }
}

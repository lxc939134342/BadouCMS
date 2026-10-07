<?php

namespace app\common\agent\services;

use app\admin\command\Crud;
use app\common\agent\AgentContext;
use app\common\service\TableDesignService;
use badou\{Menu, TableManager};
use think\console\{Input, Output};
use think\facade\Db;

/** 复用后台表设计器与 CRUD 生成器，审批参数不接受 SQL 或覆盖开关。 */
class CrudAgentService
{
    private function permit(AgentContext $context, bool $generate = false): void
    {
        if (!$context->auth->isSuperAdmin()) throw new \DomainException('一键 CRUD 仅允许超级管理员使用');
        foreach ($generate ? ['tabledesign/add', 'tabledesign/crud'] : ['tabledesign/index'] as $node) {
            if (!$context->auth->check($node, $context->adminId)) throw new \DomainException('没有权限：' . $node);
        }
    }

    public function describe(AgentContext $context): array
    {
        $this->permit($context);
        return ['types' => (new TableDesignService())->typeOptions(), 'tables' => TableManager::getTableList(),
            'field_properties' => ['name', 'type', 'comment', 'length', 'required', 'signed', 'default', 'primary', 'index', 'precision', 'scale', 'values'],
            'rules' => ['新表必须有唯一自增整数主键 id', '字段使用 snake_case，中文注释用于表单与列表名称', 'enum/set 的 values 使用英文逗号分隔，注释可写 状态:normal=正常,hidden=停用', 'content 后缀生成编辑器，image/pic/logo 生成图片上传，create_time/update_time 自动识别时间字段', '生成器创建控制器、模型、验证器、列表、新增、编辑页面、语言文件和菜单']];
    }

    public function preview(AgentContext $context, array $input): array
    {
        $this->permit($context, true);
        $unknown = array_diff(array_keys($input), ['table', 'title', 'controller', 'fields', 'snapshot']);
        if ($unknown) throw new \DomainException('不支持的参数：' . implode('、', $unknown));
        $designer = new class extends TableDesignService {
            public function validateColumn(array $field): void { $this->column($field); }
        };
        $table = $designer->bareName((string)($input['table'] ?? ''));
        $designer->assertDesignable($table);
        if ($designer->exists($table)) throw new \DomainException('数据表已存在，请使用新表名；本工具不覆盖已有数据表');
        $title = $input['title'] ?? '';
        if (!is_string($title) || trim($title) === '' || mb_strlen($title) > 80 || strpbrk($title, "\r\n\0") !== false) throw new \DomainException('请提供 1 至 80 字的功能名称');
        $controller = $input['controller'] ?? str_replace('_', '.', $table);
        if (!is_string($controller) || !preg_match('/^[a-z][a-z0-9]*(?:\.[a-z][a-z0-9]*)*$/D', $controller)) throw new \DomainException('控制器使用小写名称或点分层级，例如 sales.customer');
        $fields = $input['fields'] ?? [];
        if (!is_array($fields) || !array_is_list($fields) || count($fields) < 1 || count($fields) > 60) throw new \DomainException('请设计 1 至 60 个字段');
        $names = [];
        $primary = [];
        foreach ($fields as $field) {
            if (!is_array($field)) throw new \DomainException('每个字段必须是对象');
            if (array_diff(array_keys($field), ['name', 'type', 'comment', 'length', 'required', 'signed', 'default', 'primary', 'index', 'precision', 'scale', 'values'])) throw new \DomainException('字段包含不支持的属性');
            $name = $designer->fieldName((string)($field['name'] ?? ''));
            if (isset($names[$name])) throw new \DomainException('字段名称重复：' . $name);
            $names[$name] = true;
            if (!empty($field['primary'])) $primary[] = $field;
            foreach (['comment', 'values'] as $key) {
                if (isset($field[$key]) && (!is_string($field[$key]) || strpbrk($field[$key], "\r\n\0") !== false)) throw new \DomainException('字段注释与枚举值须为单行文字');
            }
            if (isset($field['default']) && !is_scalar($field['default']) && $field['default'] !== null) throw new \DomainException('默认值必须为标量');
            $designer->validateColumn($field);
        }
        if (count($primary) !== 1 || $primary[0]['name'] !== 'id' || !in_array($primary[0]['type'], ['int', 'bigint'], true)) throw new \DomainException('请设置唯一自增整数主键 id');
        $command = new class extends Crud {
            public function paths(string $name, string $table): array {
                $result = [];
                foreach (['controller', 'model', 'validate'] as $type) $result[] = $this->parseName($type, $name, $table, 'admin')[2];
                $path = str_replace('.', '/', $name);
                foreach (['index', 'add', 'edit'] as $view) $result[] = root_path() . 'app/admin/view/' . $path . '/' . $view . '.html';
                $result[] = root_path() . 'app/admin/lang/zh-cn/' . $path . '.php';
                return $result;
            }
        };
        $files = $command->paths($controller, $table);
        foreach ($files as $file) {
            if (file_exists($file) || is_link($file)) throw new \DomainException('生成目标已存在：' . str_replace(root_path(), '', $file));
            $directory = dirname($file);
            while (str_starts_with($directory, root_path()) && $directory !== rtrim(root_path(), '/')) {
                if (is_link($directory)) throw new \DomainException('生成目录不能使用符号链接');
                $directory = dirname($directory);
            }
        }
        if (\app\admin\model\AdminRule::where('name', $controller)->find()) throw new \DomainException('后台菜单节点已存在');
        $plan = ['table' => $table, 'title' => trim($title), 'controller' => $controller, 'fields' => $fields,
            'files' => array_map(fn ($file) => str_replace(root_path(), '', $file), $files)];
        $plan['snapshot'] = hash('sha256', json_encode([$context->tenantId, $context->adminId, $plan], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        return $plan;
    }

    public function generate(AgentContext $context, array $input): array
    {
        $key = 'agent_crud_' . substr(hash('sha256', (string)config('database.connections.' . config('database.default') . '.database')), 0, 40);
        $lock = Db::query('SELECT GET_LOCK(?, 3) AS acquired', [$key], true);
        if ((int)($lock[0]['acquired'] ?? 0) !== 1) throw new \DomainException('正在生成其他 CRUD 功能，请稍后重试');
        try {
            $plan = $this->preview($context, $input);
            if (!hash_equals($plan['snapshot'], (string)($input['snapshot'] ?? ''))) throw new \DomainException('设计方案已变化，请重新预览');
            $created = false;
            try {
                (new TableDesignService())->createTable($plan['table'], $plan['title'], 'InnoDB', $plan['fields']);
                $created = true;
                $output = new Output('buffer');
                app()->invokeClass(Crud::class)->run(new Input(['--table=' . $plan['table'], '--controller=' . $plan['controller'], '--force=false', '--menu=' . $plan['title']]), $output);
                foreach ($plan['files'] as $file) if (!is_file(root_path() . $file)) throw new \RuntimeException('生成文件缺失：' . $file);
            } catch (\Throwable $e) {
                // 本次预检保证目标不存在，只清理本次新建的文件、菜单和空表。
                foreach ($plan['files'] as $file) if (is_file(root_path() . $file)) unlink(root_path() . $file);
                if ($created) {
                    Menu::delete($plan['controller']);
                    (new TableDesignService())->dropTable($plan['table']);
                }
                throw new \DomainException('CRUD 生成失败：' . $e->getMessage(), 0, $e);
            }
            return ['created' => true, 'table' => $plan['table'], 'menu' => $plan['title'], 'route' => $plan['controller'], 'files' => $plan['files'], 'message' => '数据表和后台增删改查功能已生成，请刷新后台菜单'];
        } finally {
            Db::query('SELECT RELEASE_LOCK(?)', [$key], true);
        }
    }
}

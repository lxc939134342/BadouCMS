<?php

namespace app\common\agent\tools;

use app\common\agent\AgentContext;
use app\common\agent\AbstractAgentToolProvider;
use app\common\agent\services\ModuleAgentService;

class ModuleToolProvider extends AbstractAgentToolProvider
{
    public function guidelines(): string
    {
        return '插件管理：module_list 只看【已安装】插件，用户问“该用什么插件/有没有某功能插件”时必须先用 module_store 搜插件市场，不要只凭已安装列表就下结论，也不要凭猜测的插件名反复调用 module_info（查不到会浪费时间）；推荐“建站要装哪些插件”这类整体方案时，可用 module_store 传空关键词拿全量描述（来自本地缓存，不会重复请求）；用户问“做什么行业的网站/该用什么模板”时，也优先用 module_store 搜市场（type=template 是付费模版、type=vip_template 是 VIP 模版，可用行业词如“五金/机械/纺织”搜简介匹配），价格和 type_text 会告诉你免费/付费/VIP；module_info 看详情（已安装看本地，未安装看市场缓存）、module_config 读或改配置、module_tables 看数据表；回答插件问题时只能使用工具返回的字段和简介原文，不要新增或编造“依赖/必装前提”等字段，不要使用资料里没出现的插件名，简介里的“需安装 XX”只是说明而非硬依赖；安装(module_install)、卸载(module_uninstall)、启用/禁用(module_toggle)、升级(module_upgrade)、导入测试数据(module_import_testdata) 这些写操作必须真正调用对应工具并以工具返回为准：先向用户复述、传 confirm=true，由系统请求人工审批后执行；禁用插件不需要官方账号，启用/安装/卸载/升级/导入需要站点已登录badoucms官方账号。用户回复“确认/同意”时，你必须立刻调用对应工具，不能只口头确认；只有在工具成功返回后（例如 module_toggle 返回 toggled=true 和 state_text）才能对用户说已完成，并按 state_text 复述真实状态；不确定就重新调用 module_list 查看真实状态。绝对禁止在没有工具成功返回的情况下声称已禁用/已启用/已安装。';
    }

    public function agentTools(): array
    {
        $modules = new ModuleAgentService();

        return [
            [
                'name' => 'module_capabilities',
                'label' => '查看插件功能',
                'description' => '查看已安装插件有哪些后台功能，以及 Agent 能调用哪些操作。用于回答“CMS 插件有哪些功能”“CMS 接入了哪些 AI 工具”等问题，返回当前账号可见的菜单和已授权工具，不访问插件市场。',
                'permission' => 'agent/index',
                'properties' => [
                    ['name' => 'name', 'type' => 'string', 'description' => '插件目录名，例如 cms、geo', 'required' => true],
                ],
                'handler' => static fn (AgentContext $context, array $input): array => $modules->capabilities($context, $input['name']),
            ],
            [
                'name' => 'module_list',
                'label' => '查询已装插件',
                'description' => '列出当前站点【已安装】的插件（可带关键词/类型过滤），返回名称、版本、启用状态、作者等。要查可以安装的插件请用 module_store。',
                'permission' => 'module/index',
                'properties' => [
                    ['name' => 'keyword', 'type' => 'string', 'description' => '关键词，匹配名称/标题/作者，留空返回全部'],
                    ['name' => 'type', 'type' => 'string', 'description' => '类型过滤：module、template、vip_template 或 all，默认 all'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($modules): array {
                    return $modules->installedList(
                        is_string($input['keyword'] ?? null) ? $input['keyword'] : '',
                        is_string($input['type'] ?? null) ? $input['type'] : ''
                    );
                },
            ],
            [
                'name' => 'module_store',
                'label' => '搜索插件市场',
                'description' => '搜索插件市场（官方商店），里面有插件，也有模板：module=插件、template=付费模版、vip_template=VIP模版。用于回答“做商城/建站/SEO 用什么插件”和“做什么行业的网站该用什么模板”这类推荐问题。结果来自本地缓存（含名称、标题、版本、价格、type_text、简介、是否已安装）；可按行业词搜简介，例如“五金”“机械”“纺织”。',
                'permission' => 'module/index',
                'properties' => [
                    ['name' => 'keyword', 'type' => 'string', 'description' => '关键词，可搜标题/简介里的行业或功能，例如 五金、机械、纺织、商城、会员、支付、SEO；留空返回全部'],
                    ['name' => 'type', 'type' => 'string', 'description' => 'module 插件 / template 付费模版 / vip_template VIP模版 / all 全部；问“网站模板”时优先用 template 或 vip_template', 'enum' => ['all', 'module', 'template', 'vip_template']],
                    ['name' => 'limit', 'type' => 'integer', 'description' => '返回数量，默认 20，最多 200'],
                    ['name' => 'refresh', 'type' => 'boolean', 'description' => '是否强制刷新市场缓存，默认用缓存'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($modules): array {
                    return $modules->store(
                        is_string($input['keyword'] ?? null) ? $input['keyword'] : '',
                        is_string($input['type'] ?? null) ? $input['type'] : 'all',
                        (int)filter_var($input['limit'] ?? 20, FILTER_VALIDATE_INT),
                        filter_var($input['refresh'] ?? false, FILTER_VALIDATE_BOOLEAN) === true
                    );
                },
            ],
            [
                'name' => 'module_info',
                'label' => '插件详情',
                'description' => '查看某个插件的详情（本地 info.ini，若已登录官方账号还会返回远程信息）。',
                'permission' => 'module/info',
                'properties' => [
                    ['name' => 'name', 'type' => 'string', 'description' => '插件名称，例如 geo', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($modules): array {
                    if (!is_string($input['name'] ?? null)) {
                        throw new \DomainException('缺少插件名');
                    }
                    return $modules->info($input['name']);
                },
            ],
            [
                'name' => 'module_tables',
                'label' => '查看插件数据表',
                'description' => '列出某个插件关联的数据表。',
                'permission' => 'module/getTableList',
                'properties' => [
                    ['name' => 'name', 'type' => 'string', 'description' => '插件名称', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($modules): array {
                    if (!is_string($input['name'] ?? null)) {
                        throw new \DomainException('缺少插件名');
                    }
                    return $modules->tables($input['name']);
                },
            ],
            [
                'name' => 'module_config',
                'approval' => 'confirm',
                'label' => '插件配置',
                'description' => '读取或修改某个插件的配置。只传 name 时读取配置项；同时传 key 和 value 时修改该项（需 confirm=true）。',
                'permission' => 'module/index',
                'properties' => [
                    ['name' => 'name', 'type' => 'string', 'description' => '插件名称（配置分组名）', 'required' => true],
                    ['name' => 'key', 'type' => 'string', 'description' => '要修改的配置项名称；留空表示只读取'],
                    ['name' => 'value', 'type' => 'string', 'description' => '新的配置值'],
                    ['name' => 'confirm', 'type' => 'boolean', 'description' => '修改配置时传 true，请求人工审批'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($modules): array {
                    if (!is_string($input['name'] ?? null)) {
                        throw new \DomainException('缺少插件名');
                    }
                    $key = is_string($input['key'] ?? null) ? trim($input['key']) : '';
                    if ($key === '') {
                        return $modules->configGet($input['name']);
                    }
                    $confirm = filter_var($input['confirm'] ?? false, FILTER_VALIDATE_BOOLEAN) === true;
                    return $modules->configSet($input['name'], $key, (string)($input['value'] ?? ''), $confirm);
                },
            ],
            [
                'name' => 'module_install',
                'approval' => 'confirm',
                'label' => '安装插件',
                'description' => '安装插件（需先登录badoucms官方账号）。二次确认：先预览，用户确认后再传 confirm=true。',
                'permission' => 'module/install',
                'properties' => [
                    ['name' => 'name', 'type' => 'string', 'description' => '插件名称', 'required' => true],
                    ['name' => 'force', 'type' => 'boolean', 'description' => '是否强制安装'],
                    ['name' => 'confirm', 'type' => 'boolean', 'description' => 'false 只预览；true 请求人工审批', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($modules): array {
                    if (!is_string($input['name'] ?? null)) {
                        throw new \DomainException('缺少插件名');
                    }
                    return $modules->install(
                        $input['name'],
                        filter_var($input['force'] ?? false, FILTER_VALIDATE_BOOLEAN) === true,
                        filter_var($input['confirm'] ?? false, FILTER_VALIDATE_BOOLEAN) === true
                    );
                },
            ],
            [
                'name' => 'module_uninstall',
                'approval' => 'confirm',
                'label' => '卸载插件',
                'description' => '卸载插件。二次确认：先预览，用户确认后再传 confirm=true；drop_tables 会删除插件数据表（不可恢复）。',
                'permission' => 'module/uninstall',
                'properties' => [
                    ['name' => 'name', 'type' => 'string', 'description' => '插件名称', 'required' => true],
                    ['name' => 'force', 'type' => 'boolean', 'description' => '是否强制卸载'],
                    ['name' => 'drop_tables', 'type' => 'boolean', 'description' => '是否同时删除插件数据表，默认否'],
                    ['name' => 'confirm', 'type' => 'boolean', 'description' => 'false 只预览；true 请求人工审批', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($modules): array {
                    if (!is_string($input['name'] ?? null)) {
                        throw new \DomainException('缺少插件名');
                    }
                    return $modules->uninstall(
                        $input['name'],
                        filter_var($input['force'] ?? false, FILTER_VALIDATE_BOOLEAN) === true,
                        filter_var($input['drop_tables'] ?? false, FILTER_VALIDATE_BOOLEAN) === true,
                        filter_var($input['confirm'] ?? false, FILTER_VALIDATE_BOOLEAN) === true
                    );
                },
            ],
            [
                'name' => 'module_toggle',
                'approval' => 'confirm',
                'label' => '启停插件',
                'description' => '启用或禁用插件。禁用不需要官方账号；启用需要站点已登录badoucms官方账号。二次确认：先以 confirm=false 调用拿预览，用户明确同意后再以 confirm=true 调用执行。',
                'permission' => 'module/state',
                'properties' => [
                    ['name' => 'name', 'type' => 'string', 'description' => '插件名称', 'required' => true],
                    ['name' => 'action', 'type' => 'string', 'description' => 'enable 启用 / disable 禁用', 'enum' => ['enable', 'disable'], 'required' => true],
                    ['name' => 'force', 'type' => 'boolean', 'description' => '是否强制'],
                    ['name' => 'confirm', 'type' => 'boolean', 'description' => 'false 只预览；true 请求人工审批', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($modules): array {
                    if (!is_string($input['name'] ?? null) || !is_string($input['action'] ?? null)) {
                        throw new \DomainException('缺少插件名或操作');
                    }
                    return $modules->toggle(
                        $input['name'],
                        $input['action'],
                        filter_var($input['force'] ?? false, FILTER_VALIDATE_BOOLEAN) === true,
                        filter_var($input['confirm'] ?? false, FILTER_VALIDATE_BOOLEAN) === true
                    );
                },
            ],
            [
                'name' => 'module_upgrade',
                'approval' => 'confirm',
                'label' => '升级插件',
                'description' => '升级插件到新版本（需先登录badoucms官方账号）。二次确认：先预览，用户确认后再传 confirm=true。',
                'permission' => 'module/upgrade',
                'properties' => [
                    ['name' => 'name', 'type' => 'string', 'description' => '插件名称', 'required' => true],
                    ['name' => 'version', 'type' => 'string', 'description' => '目标版本，留空则取官方最新版本'],
                    ['name' => 'confirm', 'type' => 'boolean', 'description' => 'false 只预览；true 请求人工审批', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($modules): array {
                    if (!is_string($input['name'] ?? null)) {
                        throw new \DomainException('缺少插件名');
                    }
                    return $modules->upgrade(
                        $input['name'],
                        is_string($input['version'] ?? null) ? $input['version'] : '',
                        filter_var($input['confirm'] ?? false, FILTER_VALIDATE_BOOLEAN) === true
                    );
                },
            ],
            [
                'name' => 'module_import_testdata',
                'approval' => 'confirm',
                'label' => '导入测试数据',
                'description' => '导入插件的测试数据（会先备份数据库）。二次确认：先预览，用户确认后再传 confirm=true。',
                'permission' => 'module/testdata',
                'properties' => [
                    ['name' => 'name', 'type' => 'string', 'description' => '插件名称', 'required' => true],
                    ['name' => 'confirm', 'type' => 'boolean', 'description' => 'false 只预览；true 请求人工审批', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($modules): array {
                    if (!is_string($input['name'] ?? null)) {
                        throw new \DomainException('缺少插件名');
                    }
                    return $modules->importTestdata(
                        $input['name'],
                        filter_var($input['confirm'] ?? false, FILTER_VALIDATE_BOOLEAN) === true
                    );
                },
            ],
        ];
    }
}

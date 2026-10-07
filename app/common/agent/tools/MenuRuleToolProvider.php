<?php

namespace app\common\agent\tools;

use app\common\agent\AgentContext;
use app\common\agent\AbstractAgentToolProvider;
use app\common\agent\services\MenuAgentService;

class MenuRuleToolProvider extends AbstractAgentToolProvider
{
    public function guidelines(): string
    {
        return '当用户问的功能你无法用现有工具完成、但后台菜单里可能有该功能时，先用 menu_search 查询当前账号可见的菜单，并按工具返回的 format 用 Markdown 表格列出（菜单列保持可点击），菜单以 menu_search 返回的结果为准，不要编造；不传关键词时默认只给一级菜单，表格后提醒用户可到菜单管理自行查看或编辑。超级管理员维护菜单规则时：menu_rule_search 定位，menu_rule_create 新增，menu_rule_update_field 改单个菜单的字段（改权重用 field=weigh），menu_rule_batch_update 批量改某个分类下的菜单，menu_rule_delete 删除；删除和批量修改必须先复述目标、传 confirm=true 时由系统请求人工审批。';
    }

    public function agentTools(): array
    {
        $menus = new MenuAgentService();
        // 与后台 auth/rule 控制器一致，菜单规则只允许超级管理员维护。
        $assertSuperAdmin = static function (AgentContext $context): void {
            if (!$context->auth->isSuperAdmin()) {
                throw new \DomainException('菜单规则只能由超级管理员维护');
            }
        };

        return [
            [
                'name' => 'menu_search',
                'label' => '查找菜单',
                'description' => '按关键词查询当前后台账号能访问的菜单入口。留空关键词时默认只返回一级菜单（最多 10 条）；用于告诉用户某个功能在哪个后台菜单里，或列出菜单。',
                'permission' => 'agent/index',
                'properties' => [
                    ['name' => 'keyword', 'type' => 'string', 'description' => '功能关键词，例如“会员”“内容池”“模型配置”；留空时返回一级菜单'],
                    ['name' => 'level', 'type' => 'integer', 'description' => '菜单层级：1=一级（默认），2=二级，0=全部层级；传关键词时自动查全部层级'],
                    ['name' => 'limit', 'type' => 'integer', 'description' => '最多返回数量，默认 10，最多 50'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($menus): array {
                    $keyword = is_string($input['keyword'] ?? null) ? trim($input['keyword']) : '';
                    $level = (int)filter_var($input['level'] ?? 1, FILTER_VALIDATE_INT);
                    $limit = (int)filter_var($input['limit'] ?? 10, FILTER_VALIDATE_INT);
                    return $menus->navigation(
                        $context->auth->getMenus($context->adminId),
                        $keyword,
                        $level,
                        $limit ?: 10,
                        $context->auth->check('auth.rule/index', $context->adminId)
                    );
                },
            ],
            [
                'name' => 'menu_rule_search',
                'label' => '查询菜单规则',
                'description' => '查询后台菜单规则（节点表），可按规则名/标题或父级过滤。返回 id、pid、name、title、url、type、ismenu、weigh、status 等字段，用于管理菜单前先定位目标。',
                'permission' => 'auth.rule/index',
                'properties' => [
                    ['name' => 'keyword', 'type' => 'string', 'description' => '规则名或标题关键词；留空返回权重靠前的规则'],
                    ['name' => 'pid', 'type' => 'integer', 'description' => '只看某个分类（父级 ID）下的直接子菜单；不传表示全部'],
                    ['name' => 'limit', 'type' => 'integer', 'description' => '返回数量，默认 10，最多 200'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($menus, $assertSuperAdmin): array {
                    $assertSuperAdmin($context);
                    if (!is_string($input['keyword'] ?? '')) {
                        throw new \DomainException('关键词格式无效');
                    }
                    $limit = (int)filter_var($input['limit'] ?? 10, FILTER_VALIDATE_INT);
                    $pid = filter_var($input['pid'] ?? -1, FILTER_VALIDATE_INT);
                    $pid = $pid === false ? -1 : (int)$pid;
                    $result = $menus->search($input['keyword'] ?? '', $limit ?: 10, $pid);
                    $result['format'] = '用 Markdown 表格输出，列为：ID | 标题 | 规则名 | 权重 | 状态。';
                    return $result;
                },
            ],
            [
                'name' => 'menu_rule_create',
                'label' => '新增菜单规则',
                'description' => '新增一个后台菜单规则。name 是规则名（如 geo.foo/index），title 是菜单标题；其余字段可选：pid、type(0目录/1菜单/2按钮)、ismenu、url、icon、weigh、status、menutype、is_quick、remark。',
                'permission' => 'auth.rule/add',
                'properties' => [
                    ['name' => 'name', 'type' => 'string', 'description' => '规则名，只能是小写字母、数字、下划线和 / .', 'required' => true],
                    ['name' => 'title', 'type' => 'string', 'description' => '菜单标题', 'required' => true],
                    ['name' => 'pid', 'type' => 'integer', 'description' => '父级菜单 ID，0 表示顶级'],
                    ['name' => 'type', 'type' => 'integer', 'description' => '0=菜单目录，1=菜单项，2=按钮，默认 1'],
                    ['name' => 'ismenu', 'type' => 'integer', 'description' => '是否在侧边栏显示，1 显示 / 0 不显示'],
                    ['name' => 'url', 'type' => 'string', 'description' => '跳转地址，留空则用规则名'],
                    ['name' => 'icon', 'type' => 'string', 'description' => '图标 class，例如 fa fa-cog'],
                    ['name' => 'weigh', 'type' => 'integer', 'description' => '权重，越大越靠前，默认 0'],
                    ['name' => 'status', 'type' => 'string', 'description' => 'normal 显示 / hidden 隐藏', 'enum' => ['normal', 'hidden']],
                    ['name' => 'menutype', 'type' => 'string', 'description' => '_iframe 选项卡 / _blank 新链接', 'enum' => ['_iframe', '_blank']],
                    ['name' => 'remark', 'type' => 'string', 'description' => '备注'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($menus, $assertSuperAdmin): array {
                    $assertSuperAdmin($context);
                    return $menus->create($input);
                },
            ],
            [
                'name' => 'menu_rule_update_field',
                'label' => '修改菜单规则',
                'description' => '按菜单规则 ID 修改一个字段。field 支持 name、title、url、icon、condition、remark、extend、menutype、type、ismenu、is_quick、status、weigh、pid。修改权重用 field=weigh。',
                'permission' => 'auth.rule/edit',
                'properties' => [
                    ['name' => 'rule_id', 'type' => 'integer', 'description' => '要修改的菜单规则 ID', 'required' => true],
                    ['name' => 'field', 'type' => 'string', 'description' => '要修改的字段名', 'required' => true],
                    ['name' => 'value', 'type' => 'string', 'description' => '新的字段值，数字也作为字符串传入', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($menus, $assertSuperAdmin): array {
                    $assertSuperAdmin($context);
                    $id = filter_var($input['rule_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($id === false || !is_string($input['field'] ?? null) || !is_string($input['value'] ?? null)) {
                        throw new \DomainException('菜单 ID 或参数无效');
                    }
                    return $menus->updateField($id, $input['field'], $input['value']);
                },
            ],
            [
                'name' => 'menu_rule_batch_update',
                'approval' => 'confirm',
                'label' => '批量修改菜单',
                'description' => '批量修改某个分类（父级菜单）下所有子菜单的同一个字段，支持 weigh、status、ismenu、is_quick。必须二次确认：先复述影响数量，得到用户确认后再传 confirm=true；confirm 不为 true 时只返回预览。',
                'permission' => 'auth.rule/edit',
                'properties' => [
                    ['name' => 'parent_id', 'type' => 'integer', 'description' => '分类/父级菜单 ID', 'required' => true],
                    ['name' => 'field', 'type' => 'string', 'description' => '要批量修改的字段', 'enum' => ['weigh', 'status', 'ismenu', 'is_quick'], 'required' => true],
                    ['name' => 'value', 'type' => 'string', 'description' => '新的字段值', 'required' => true],
                    ['name' => 'confirm', 'type' => 'boolean', 'description' => 'false 只预览；true 请求人工审批；否则只返回预览', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($menus, $assertSuperAdmin): array {
                    $assertSuperAdmin($context);
                    $parentId = filter_var($input['parent_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($parentId === false || !is_string($input['field'] ?? null) || !is_string($input['value'] ?? null)) {
                        throw new \DomainException('分类或参数无效');
                    }
                    if (filter_var($input['confirm'] ?? false, FILTER_VALIDATE_BOOLEAN) !== true) {
                        return $menus->batchPreview($parentId, $input['field'], $input['value']);
                    }
                    return $menus->batchUpdate($parentId, $input['field'], $input['value']);
                },
            ],
            [
                'name' => 'menu_rule_delete',
                'approval' => 'confirm',
                'label' => '删除菜单规则',
                'description' => '删除一个菜单规则及其全部子菜单。必须二次确认：先用 menu_rule_search 确定目标，向用户复述后再调用本工具并传 confirm=true；confirm 不为 true 时只返回预览，不会删除。',
                'permission' => 'auth.rule/del',
                'properties' => [
                    ['name' => 'rule_id', 'type' => 'integer', 'description' => '要删除的菜单规则 ID', 'required' => true],
                    ['name' => 'confirm', 'type' => 'boolean', 'description' => 'false 只预览；true 请求人工审批；否则不执行删除', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($menus, $assertSuperAdmin): array {
                    $assertSuperAdmin($context);
                    $id = filter_var($input['rule_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($id === false) {
                        throw new \DomainException('菜单 ID 无效');
                    }
                    if (filter_var($input['confirm'] ?? false, FILTER_VALIDATE_BOOLEAN) !== true) {
                        return $menus->deletePreview($id);
                    }
                    return $menus->delete($id);
                },
            ],
        ];
    }
}

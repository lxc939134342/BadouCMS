<?php

namespace app\common\agent\tools;

use app\common\agent\AgentContext;
use app\common\agent\AbstractAgentToolProvider;
use app\common\agent\services\AdminGroupAgentService;

class AdminGroupToolProvider extends AbstractAgentToolProvider
{
    public function agentTools(): array
    {
        $groups = new AdminGroupAgentService();

        return [
            [
                'name' => 'admin_group_search',
                'label' => '查询后台角色组',
                'description' => '查询当前账号可见的后台角色组，只返回 ID、父级、名称和状态，不返回权限规则明细。',
                'permission' => 'auth.group/index',
                'properties' => [
                    ['name' => 'keyword', 'type' => 'string', 'description' => '角色组 ID 或名称；留空返回可见角色组'],
                    ['name' => 'limit', 'type' => 'integer', 'description' => '返回数量，默认 20，最多 50'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($groups): array {
                    $limit = filter_var($input['limit'] ?? 20, FILTER_VALIDATE_INT);
                    if (!is_string($input['keyword'] ?? '') || $limit === false || $limit < 1) {
                        throw new \DomainException('角色组查询参数无效');
                    }
                    return $groups->search($context->auth, (string)($input['keyword'] ?? ''), $limit);
                },
            ],
            [
                'name' => 'admin_group_rules',
                'label' => '查看角色组权限',
                'description' => '查看一个可见后台角色组绑定的权限节点名称和标题。超级管理员组只返回“全部权限”，不展开节点。',
                'permission' => 'auth.group/index',
                'properties' => [
                    ['name' => 'group_id', 'type' => 'integer', 'description' => '角色组 ID', 'required' => true],
                    ['name' => 'limit', 'type' => 'integer', 'description' => '返回节点数量，默认 50，最多 100'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($groups): array {
                    $id = filter_var($input['group_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    $limit = filter_var($input['limit'] ?? 50, FILTER_VALIDATE_INT);
                    if ($id === false || $limit === false || $limit < 1) {
                        throw new \DomainException('角色组权限参数无效');
                    }
                    return $groups->rules($context->auth, $id, $limit);
                },
            ],
            [
                'name' => 'admin_group_members',
                'label' => '查询角色组管理员',
                'description' => '查看一个可见后台角色组下的管理员，只返回 ID、用户名、昵称和状态。',
                'permission' => 'auth.group/index',
                'properties' => [
                    ['name' => 'group_id', 'type' => 'integer', 'description' => '角色组 ID', 'required' => true],
                    ['name' => 'limit', 'type' => 'integer', 'description' => '返回数量，默认 20，最多 50'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($groups): array {
                    $id = filter_var($input['group_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    $limit = filter_var($input['limit'] ?? 20, FILTER_VALIDATE_INT);
                    if ($id === false || $limit === false || $limit < 1) {
                        throw new \DomainException('角色组成员参数无效');
                    }
                    return $groups->members($context->auth, $id, $limit);
                },
            ],
        ];
    }
}

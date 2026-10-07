<?php

namespace app\common\agent\tools;

use app\common\agent\AgentContext;
use app\common\agent\AbstractAgentToolProvider;
use app\common\agent\services\UserGroupAgentService;

class UserGroupToolProvider extends AbstractAgentToolProvider
{
    public function agentTools(): array
    {
        $groups = new UserGroupAgentService();

        return [
            [
                'name' => 'user_group_search',
                'label' => '查询会员角色组',
                'description' => '查询会员角色组，返回 ID、名称和状态。不返回前台权限节点明细。',
                'permission' => 'user.group/index',
                'properties' => [
                    ['name' => 'keyword', 'type' => 'string', 'description' => '角色组 ID 或名称；留空返回全部'],
                    ['name' => 'limit', 'type' => 'integer', 'description' => '返回数量，默认 20，最多 50'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($groups): array {
                    $limit = filter_var($input['limit'] ?? 20, FILTER_VALIDATE_INT);
                    if (!is_string($input['keyword'] ?? '') || $limit === false || $limit < 1) {
                        throw new \DomainException('会员角色组查询参数无效');
                    }
                    return $groups->search((string)($input['keyword'] ?? ''), $limit);
                },
            ],
            [
                'name' => 'user_group_update_field',
                'label' => '修改会员角色组',
                'description' => '修改会员角色组的名称或状态。field 只能是 name 或 status，status 使用 normal 或 hidden。不能通过本工具改权限节点。',
                'permission' => 'user.group/edit',
                'properties' => [
                    ['name' => 'group_id', 'type' => 'integer', 'description' => '会员角色组 ID', 'required' => true],
                    ['name' => 'field', 'type' => 'string', 'description' => '字段名', 'enum' => ['name', 'status'], 'required' => true],
                    ['name' => 'value', 'type' => 'string', 'description' => '新的字段值', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($groups): array {
                    $id = filter_var($input['group_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($id === false || !is_string($input['field'] ?? null) || !is_string($input['value'] ?? null)) {
                        throw new \DomainException('会员角色组参数无效');
                    }
                    return $groups->updateField($id, $input['field'], $input['value']);
                },
            ],
            [
                'name' => 'user_group_members',
                'label' => '查询角色组会员',
                'description' => '查看某个会员角色组下的会员，只返回 ID、用户名、昵称和状态。',
                'permission' => 'user.group/index',
                'properties' => [
                    ['name' => 'group_id', 'type' => 'integer', 'description' => '会员角色组 ID', 'required' => true],
                    ['name' => 'limit', 'type' => 'integer', 'description' => '返回数量，默认 20，最多 20'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($groups): array {
                    $id = filter_var($input['group_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    $limit = filter_var($input['limit'] ?? 20, FILTER_VALIDATE_INT);
                    if ($id === false || $limit === false || $limit < 1) {
                        throw new \DomainException('会员角色组成员参数无效');
                    }
                    return $groups->members($id, $limit);
                },
            ],
        ];
    }
}

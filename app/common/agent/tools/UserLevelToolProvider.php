<?php

namespace app\common\agent\tools;

use app\common\agent\AgentContext;
use app\common\agent\AbstractAgentToolProvider;
use app\common\agent\services\UserLevelAgentService;

class UserLevelToolProvider extends AbstractAgentToolProvider
{
    public function agentTools(): array
    {
        $levels = new UserLevelAgentService();

        return [
            [
                'name' => 'user_level_search',
                'label' => '查询会员等级',
                'description' => '查询会员等级，返回等级编号、名称、描述、状态和积分上下限。',
                'permission' => 'user.level/index',
                'properties' => [
                    ['name' => 'keyword', 'type' => 'string', 'description' => '等级 ID、编号或名称；留空返回全部'],
                    ['name' => 'limit', 'type' => 'integer', 'description' => '返回数量，默认 20，最多 50'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($levels): array {
                    $limit = filter_var($input['limit'] ?? 20, FILTER_VALIDATE_INT);
                    if (!is_string($input['keyword'] ?? '') || $limit === false || $limit < 1) {
                        throw new \DomainException('会员等级查询参数无效');
                    }
                    return $levels->search((string)($input['keyword'] ?? ''), $limit);
                },
            ],
            [
                'name' => 'user_level_update_field',
                'label' => '修改会员等级',
                'description' => '修改会员等级的一个字段。允许 gname、description、status、gcode、lscore、uscore。status 使用 1 启用或 0 停用。',
                'permission' => 'user.level/edit',
                'properties' => [
                    ['name' => 'level_id', 'type' => 'integer', 'description' => '会员等级 ID', 'required' => true],
                    ['name' => 'field', 'type' => 'string', 'description' => '字段名', 'enum' => ['gname', 'description', 'status', 'gcode', 'lscore', 'uscore'], 'required' => true],
                    ['name' => 'value', 'type' => 'string', 'description' => '新的字段值', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($levels): array {
                    $id = filter_var($input['level_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($id === false || !is_string($input['field'] ?? null) || !is_string($input['value'] ?? null)) {
                        throw new \DomainException('会员等级参数无效');
                    }
                    return $levels->updateField($id, $input['field'], $input['value']);
                },
            ],
        ];
    }
}

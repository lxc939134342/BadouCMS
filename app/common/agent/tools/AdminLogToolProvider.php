<?php

namespace app\common\agent\tools;

use app\common\agent\AgentContext;
use app\common\agent\AbstractAgentToolProvider;
use app\common\agent\services\AdminLogAgentService;

class AdminLogToolProvider extends AbstractAgentToolProvider
{
    public function agentTools(): array
    {
        $logs = new AdminLogAgentService();

        return [
            [
                'name' => 'admin_log_search',
                'label' => '查询后台日志',
                'description' => '只读查询管理员操作日志。可按用户名、日志标题或 URL 关键词过滤。不返回请求体，避免带出密码和令牌。非超级管理员只能看自己权限范围内的管理员日志。',
                'permission' => 'auth.adminlog/index',
                'properties' => [
                    ['name' => 'keyword', 'type' => 'string', 'description' => '用户名、标题或 URL 关键词；留空返回最近日志'],
                    ['name' => 'limit', 'type' => 'integer', 'description' => '返回数量，默认 20，最多 50'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($logs): array {
                    $limit = filter_var($input['limit'] ?? 20, FILTER_VALIDATE_INT);
                    if (!is_string($input['keyword'] ?? '') || $limit === false || $limit < 1) {
                        throw new \DomainException('日志查询参数无效');
                    }
                    return $logs->search($context->auth, (string)($input['keyword'] ?? ''), $limit);
                },
            ],
        ];
    }
}

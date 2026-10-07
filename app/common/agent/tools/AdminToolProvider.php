<?php

namespace app\common\agent\tools;

use app\common\agent\AgentContext;
use app\common\agent\AbstractAgentToolProvider;
use app\common\agent\services\AdminAgentService;

class AdminToolProvider extends AbstractAgentToolProvider
{
    public function agentTools(): array
    {
        $admins = new AdminAgentService();

        return [
            [
                'name' => 'admin_search',
                'label' => '查询管理员',
                'description' => '按管理员 ID、用户名或昵称查询当前账号可见的后台管理员，返回资料和所属角色组。不返回密码。',
                'permission' => 'auth.admin/index',
                'properties' => [
                    ['name' => 'keyword', 'type' => 'string', 'description' => '管理员 ID、用户名或昵称；留空返回最近的管理员'],
                    ['name' => 'limit', 'type' => 'integer', 'description' => '返回数量，默认 20，最多 20'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($admins): array {
                    $limit = filter_var($input['limit'] ?? 20, FILTER_VALIDATE_INT);
                    if (!is_string($input['keyword'] ?? '') || $limit === false || $limit < 1) {
                        throw new \DomainException('管理员查询参数无效');
                    }
                    return $admins->search($context->auth, (string)($input['keyword'] ?? ''), $limit);
                },
            ],
            [
                'name' => 'admin_update_field',
                'label' => '修改管理员资料',
                'description' => '修改一个可见管理员的资料。只允许 nickname、email、mobile、status、avatar。status 使用 normal 或 hidden。头像必须是当前管理员已上传的图片附件 URL。',
                'permission' => 'auth.admin/edit',
                'properties' => [
                    ['name' => 'admin_id', 'type' => 'integer', 'description' => '管理员 ID', 'required' => true],
                    ['name' => 'field', 'type' => 'string', 'description' => '字段名', 'enum' => ['nickname', 'email', 'mobile', 'status', 'avatar'], 'required' => true],
                    ['name' => 'value', 'type' => 'string', 'description' => '新的字段值', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($admins): array {
                    $id = filter_var($input['admin_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($id === false || !is_string($input['field'] ?? null) || !is_string($input['value'] ?? null)) {
                        throw new \DomainException('管理员资料参数无效');
                    }
                    return $admins->updateField($context->auth, $id, $input['field'], $input['value'], $context->adminId);
                },
            ],
            [
                'name' => 'admin_reset_password',
                'label' => '重置管理员密码',
                'approval' => 'confirm',
                'description' => '重置一个可见管理员的登录密码，并使其现有登录失效。必须二次确认：先查询并复述管理员，用户明确确认后再传 confirm=true 和新密码。confirm 不为 true 时只返回预览。',
                'permission' => 'auth.admin/edit',
                'properties' => [
                    ['name' => 'admin_id', 'type' => 'integer', 'description' => '管理员 ID', 'required' => true],
                    ['name' => 'password', 'type' => 'string', 'description' => '新密码，3 到 20 位；预览时可以留空'],
                    ['name' => 'confirm', 'type' => 'boolean', 'description' => 'false 只预览；true 请求人工审批', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($admins): array {
                    $id = filter_var($input['admin_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($id === false) {
                        throw new \DomainException('管理员 ID 无效');
                    }
                    if (filter_var($input['confirm'] ?? false, FILTER_VALIDATE_BOOLEAN) !== true) {
                        return $admins->resetPasswordPreview($context->auth, $id);
                    }
                    if (!is_string($input['password'] ?? null)) {
                        throw new \DomainException('新密码无效');
                    }
                    return $admins->resetPassword($context->auth, $id, $input['password']);
                },
            ],
            [
                'name' => 'admin_delete',
                'label' => '删除管理员',
                'approval' => 'confirm',
                'description' => '删除一个可见管理员。不能删除当前登录人，也不能删除仍在运营的企业负责人。必须二次确认：confirm 不为 true 时只返回预览。',
                'permission' => 'auth.admin/del',
                'properties' => [
                    ['name' => 'admin_id', 'type' => 'integer', 'description' => '管理员 ID', 'required' => true],
                    ['name' => 'confirm', 'type' => 'boolean', 'description' => 'false 只预览；true 请求人工审批', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($admins): array {
                    $id = filter_var($input['admin_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($id === false) {
                        throw new \DomainException('管理员 ID 无效');
                    }
                    if (filter_var($input['confirm'] ?? false, FILTER_VALIDATE_BOOLEAN) !== true) {
                        return $admins->deletePreview($context->auth, $id);
                    }
                    return $admins->delete($context->auth, $id);
                },
            ],
        ];
    }
}

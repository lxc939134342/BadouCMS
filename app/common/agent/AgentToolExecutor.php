<?php

namespace app\common\agent;

use think\facade\Log;
use badou\Server;

class AgentToolExecutor
{
    private array $approvalFailures = [];
    public function allowed(array $tool, AgentContext $context): bool
    {
        if (($tool['module'] ?? 'core') !== 'core' && !$this->moduleEnabled($tool['module'])) return false;
        return $context->adminId > 0 && $context->auth->check($tool['permission'], $context->adminId);
    }

    protected function moduleEnabled(string $name): bool
    {
        foreach (Server::getInstalldModuleList() as $module) {
            if ($module['name'] === $name) return (int)($module['state'] ?? 0) === 1;
        }
        return false;
    }

    public function execute(array $tool, AgentContext $context, array $input, ?string $callId = null): array
    {
        $context->control?->check(true);
        if (!$this->allowed($tool, $context)) {
            throw new \DomainException('当前后台账号没有执行该操作的权限');
        }

        $this->validateApproval($tool, $context, $input, $callId);
        $result = ($tool['handler'])($context, $input);
        Log::info('后台 Agent 工具调用：管理员={admin_id}，站点={tenant_id}，工具={tool}', [
            'admin_id' => $context->adminId,
            'tenant_id' => $context->tenantId,
            'tool' => $tool['name'],
            'input_fields' => array_keys($input),
        ]);
        return $result;
    }

    /** 只读预检供审批和执行共同使用，不能在这里调用工具的业务 handler。 */
    public function validateApproval(array $tool, AgentContext $context, array $input, ?string $callId = null, bool $beforeApproval = false): void
    {
        if (!isset($tool['approval_validate'])) return;
        $key = hash('sha256', json_encode([$context->tenantId, $context->adminId, $context->chatId,
            $tool['name'], $callId, $input], JSON_THROW_ON_ERROR));
        // 预检失败的这次调用必须失败，即使两次校验之间数据恢复，也不能绕过审批写入。
        if (isset($this->approvalFailures[$key])) throw $this->approvalFailures[$key];
        try {
            $context->control?->check(true);
            if (!$this->allowed($tool, $context)) throw new \DomainException('当前后台账号没有执行该操作的权限');
            ($tool['approval_validate'])($context, $input);
        } catch (\DomainException $e) {
            if ($beforeApproval) $this->approvalFailures[$key] = $e;
            throw $e;
        }
    }
}

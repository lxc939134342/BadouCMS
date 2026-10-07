<?php

namespace app\common\agent;

use app\common\library\AdminAuth;

/** 当前后台请求的身份由服务器创建，工具参数不能覆盖它。 */
class AgentContext
{
    public array $submittedTaskIds = [];
    public function __construct(
        public readonly AdminAuth $auth,
        public readonly int $adminId,
        public readonly int $tenantId,
        public readonly int $chatId = 0,
        public readonly bool $autoApprove = false,
        public readonly ?AgentRunControl $control = null,
        public readonly string $conversationVersion = '',
    ) {
    }

    public static function fromAuth(AdminAuth $auth, int $chatId = 0, bool $autoApprove = false, ?AgentRunControl $control = null, string $conversationVersion = ''): self
    {
        return new self($auth, (int)$auth->id, 0, $chatId, $autoApprove, $control, $conversationVersion);
    }
}

<?php

namespace app\common\agent;

/** 可重新读取数据并生成审批的过期请求，不能直接执行旧参数。 */
class ApprovalExpiredException extends \DomainException
{
    public function __construct(string $message, public readonly string $instructions = '')
    {
        parent::__construct($message);
    }
}

<?php

namespace app\common\agent;

/** 用户主动停止，不作为模型或工具错误重试。 */
class AgentStoppedException extends \DomainException
{
    public function __construct() { parent::__construct('已停止生成'); }
}

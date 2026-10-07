<?php

namespace app\common\agent;

abstract class AbstractAgentToolProvider implements AgentToolProviderInterface
{
    /** 只在该组工具可用时向模型提供业务说明。 */
    public function guidelines(): string
    {
        return '';
    }

    /** 插件自行声明常驻工具，通用助手不维护插件工具名称。 */
    public function residentTools(): array
    {
        return [];
    }
}

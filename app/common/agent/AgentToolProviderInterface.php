<?php

namespace app\common\agent;

interface AgentToolProviderInterface
{
    /** @return array<int, AgentToolInterface|array> */
    public function agentTools(): array;
}

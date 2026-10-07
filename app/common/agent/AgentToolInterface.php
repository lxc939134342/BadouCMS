<?php

namespace app\common\agent;

use NeuronAI\Tools\ToolPropertyInterface;

interface AgentToolInterface
{
    public function name(): string;

    public function description(): string;

    /** @return array<int, ToolPropertyInterface|array> */
    public function properties(): array;

    /** 对应后台已有的操作权限节点。 */
    public function permission(): string;

    public function execute(AgentContext $context, array $input): array;
}

<?php

namespace app\common\agent\middleware;

use app\common\agent\AgentRunControl;
use NeuronAI\Agent\AgentResources;
use NeuronAI\Agent\AgentState;
use NeuronAI\Agent\Middleware\AgentMiddleware;
use NeuronAI\Agent\Nodes\AgentNodeInterface;
use NeuronAI\Workflow\Events\Event;

class RunControlMiddleware extends AgentMiddleware
{
    public function __construct(private AgentRunControl $control) {}

    protected function beforeAgentNode(AgentNodeInterface $node, Event $event, AgentState $state, AgentResources $resources): void
    {
        $this->control->check(true);
    }

    protected function afterAgentNode(AgentNodeInterface $node, Event $event, AgentState $state, AgentResources $resources): void
    {
        $this->control->check(true);
    }
}

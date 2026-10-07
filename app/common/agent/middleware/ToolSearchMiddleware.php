<?php

namespace app\common\agent\middleware;

use NeuronAI\Agent\AgentResources;
use NeuronAI\Agent\AgentState;
use NeuronAI\Agent\Nodes\AgentNodeInterface;
use NeuronAI\Chat\Messages\ToolResultMessage;
use NeuronAI\Workflow\Events\Event;

/** 实际检索与恢复已发现工具使用同一算法，避免找到工具后仍无法调用。 */
class ToolSearchMiddleware extends \NeuronAI\Agent\Middleware\ToolSearchMiddleware
{
    protected function beforeAgentNode(AgentNodeInterface $node, Event $event, AgentState $state, AgentResources $resources): void
    {
        // 注册中心不覆盖同名工具，先注册本地检索器，再复用官方中间件流程。
        $resources->tools->add(new ToolSearchTool($this->toolPool, $this->topN));
        parent::beforeAgentNode($node, $event, $state, $resources);
    }

    protected function discoverFromMessages(array $messages): array
    {
        $finder = new ToolSearchTool($this->toolPool, $this->topN);
        $discovered = [];
        foreach ($messages as $message) {
            if (!$message instanceof ToolResultMessage) continue;
            foreach ($message->getToolCalls() as $call) {
                $query = $call->getInput('query');
                if ($call->getName() !== $finder->getName() || !is_string($query)) continue;
                foreach ($finder->search($query) as $tool) $discovered[$tool->getName()] = $tool;
            }
        }
        return array_values($discovered);
    }
}

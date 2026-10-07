<?php

namespace app\common\agent;

use NeuronAI\Observability\ObservabilityEvent;
use NeuronAI\Workflow\Observability\WorkflowError;
use think\facade\Log;

/** 把 Neuron 的 PSR-14 事件写入 ThinkPHP 日志。 */
class AgentLogListener
{
    private const WATCH = [
        'inference-start',
        'inference-stop',
        'tool-calling',
        'tool-called',
        'error',
        'workflow-end',
    ];

    public function __invoke(ObservabilityEvent $event): void
    {
        if (!in_array($event->name(), self::WATCH, true)) {
            return;
        }

        $source = $event->source ?? $event;
        $detail = $source::class;
        if (isset($event->tool) && is_object($event->tool)) {
            $detail .= ' tool=' . $event->tool->getName();
        } elseif ($event instanceof WorkflowError) {
            $detail .= ' error=' . $event->exception->getMessage();
        }

        Log::info('Agent 事件：{event}，{source}，分支={branch}', [
            'event' => $event->name(),
            'source' => $detail,
            'branch' => $event->branchId,
        ]);
    }
}

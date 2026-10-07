<?php

namespace app\common\agent;

use NeuronAI\Agent\AgentState;
use NeuronAI\Agent\Interrupt\ApprovalTranslator;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Stream\Chunks\{TextChunk, ToolCallChunk, ToolResultChunk};
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Workflow\Executor\ExecutionRequest;
use NeuronAI\Workflow\WorkflowStatus;
use NeuronAI\Tools\ToolOutput;

/** 公共对话入口：插件可以传入自己的 BaseAgent 子类，复用流事件和审批流程。 */
class AgentService
{
    public function reply(AgentContext $context, array $history, string $question, ?callable $progress = null): array
    {
        $messages = [];
        foreach (array_slice($history, -20) as $item) {
            $content = mb_substr((string)($item['content'] ?? ''), 0, 4000);
            if ($content === '') continue;
            $messages[] = ($item['role'] ?? '') === 'assistant' ? new AssistantMessage($content) : new UserMessage($content);
        }
        return $this->ask(new AdminAgent($context, $progress, $messages), $question, $progress);
    }

    public function ask(BaseAgent $agent, string $question, ?callable $progress = null): array
    {
        $run = $agent->inspect();
        if ($run !== null && $run->status !== WorkflowStatus::Failed) {
            throw new \DomainException('当前会话仍有未完成的操作，请刷新会话并先处理审批');
        }
        if ($run !== null) {
            if (!$agent->discardRun($run->runId, $run->executionAttempt)) throw new \DomainException('会话正在执行，请稍后重试');
        }
        $progress && $progress('progress', ['message' => '正在分析问题']);
        $execution = ExecutionRequest::start(new \NeuronAI\Agent\Events\AgentStartEvent(
            [new UserMessage($question)], new \NeuronAI\Agent\AgentRunOptions(
                stream: $progress !== null && (bool)config('agent.stream_tools', true)
            )
        ));
        return $this->execute($agent, $execution, $question, $progress);
    }

    /** 浏览器只提交决策与状态版本；工具参数一律从持久化的审批请求读取。 */
    public function resume(BaseAgent $agent, string $runId, int $attempt, string $decision, ?callable $progress = null): array
    {
        $run = $agent->inspect();
        if (!in_array($decision, ['approve', 'reject', 'refresh'], true) || $run === null || $run->runId !== $runId
            || $run->executionAttempt !== $attempt || $agent->pendingApprovals() === []) {
            throw new \DomainException('审批已处理或状态已变化，请刷新会话');
        }
        $tools = array_column($agent->toolDefinitions(), null, 'name');
        $actions = $agent->pendingApprovals();
        if ($decision === 'refresh') {
            if (count($actions) !== 1 || $actions[0]->name !== 'cms_language_translate'
                || !isset($tools[$actions[0]->name])) throw new \DomainException('当前操作不支持重新生成译文，请刷新会话');
            $display = $agent->approvalDisplay($actions[0]->name, $actions[0]->inputs);
            if (!($display['can_refresh'] ?? false)) throw new \DomainException('当前预览未失效或权限已变化，请刷新会话');
        }
        $missing = array_filter($agent->pendingApprovals(), fn ($action) => !isset($tools[$action->name]));
        if ($decision === 'reject') {
            foreach ($missing as $action) {
                // 停用的工具只恢复审批拒绝流程；这个占位工具永远不执行业务。
                $agent->addTool(new class($action->name) extends \NeuronAI\Tools\Tool {
                    public function __construct(string $name) { $this->name = $name; }
                    protected function properties(): array { return []; }
                    protected function approvalPolicy(): bool|string { return true; }
                    public function __invoke(...$input): string { throw new \DomainException('工具已停用'); }
                });
            }
        }
        $decisions = [];
        foreach ($agent->pendingApprovals() as $action) {
            if ($decision === 'approve' && !isset($tools[$action->name])) {
                throw new \DomainException('工具已停用或当前账号权限已变化，请取消操作并开启新会话');
            }
            $decisions[$action->id] = $decision === 'refresh' ? ['reject',
                '旧翻译预览已失效，不执行旧保存。请使用原审批相同的源/目标语言、对象、栏目范围、游标和补译选项，'
                . '先调用 cms_language_translation_preview 重新读取本轮数据；按新预览生成译文并提交新的保存审批。'
                . '不能沿用旧 snapshot 或扩大范围；没有待翻译项时按实际跳过原因说明。'] : $decision;
        }
        $payload = (new ApprovalTranslator())->translate($decisions, $run->interrupt);
        return $this->execute($agent, ExecutionRequest::resume($payload, $runId, $attempt),
            (string)($run->startEvent->messages[0]?->getContent() ?? ''), $progress);
    }

    public function approval(BaseAgent $agent): ?array
    {
        $actions = $agent->pendingApprovals();
        if ($actions === []) return null;
        $run = $agent->inspect();
        return ['run_id' => $run->runId, 'attempt' => $run->executionAttempt, 'actions' => array_map(
            fn ($action) => ['id' => $action->id, 'name' => $action->name, 'label' => $agent->toolLabel($action->name),
                'reason' => $action->reason, 'inputs' => $action->inputs,
                'display' => $agent->approvalDisplay($action->name, $action->inputs)], $actions
        )];
    }

    /** 输出成功后才清理完成状态；落库失败时可从数据库恢复结果。 */
    public function acknowledge(BaseAgent $agent, array $reply): void
    {
        if ($reply['approval'] === null && $agent->inspect()?->status === WorkflowStatus::Completed) $agent->acknowledge($reply['run_id']);
    }

    public function recover(BaseAgent $agent): ?array
    {
        $run = $agent->inspect();
        if ($run?->status !== WorkflowStatus::Completed) return null;
        return $this->execute($agent, ExecutionRequest::resume(null, $run->runId, $run->executionAttempt),
            (string)($run->startEvent->messages[0]?->getContent() ?? ''), null);
    }

    private function execute(BaseAgent $agent, ExecutionRequest $execution, string $question, ?callable $progress): array
    {
        $toolCalls = [];
        $text = '';
        try {
            if ($progress !== null) {
                $stream = $agent->events($execution);
                foreach ($stream as $event) {
                    if ($event instanceof TextChunk && $event->content !== '') {
                        $text .= $event->content;
                        $progress('delta', ['text' => $event->content]);
                    } elseif ($event instanceof ToolCallChunk) {
                        $text = '';
                        $progress('tool_start', ['name' => $agent->toolLabel($event->tool->getName())]);
                    } elseif ($event instanceof ToolResultChunk) {
                        $name = $event->tool->getName();
                        $label = $agent->toolLabel($name);
                        $error = $agent->takeToolError($name);
                        $output = $event->tool->getResult();
                        // Neuron 的参数校验直接返回 ToolOutput，不经过异常处理器。
                        if ($error === null && $output instanceof ToolOutput && $output->isError()) $error = '工具调用失败：' . $output->getText();
                        if ($error !== null) $progress('tool_error', ['name' => $label, 'message' => $error]);
                        else {
                            $toolCalls[] = $label;
                            $progress('tool_done', ['name' => $label]);
                        }
                    }
                }
                $state = $stream->getReturn();
            } else $state = $agent->run($execution);
        } catch (AgentStoppedException $e) {
            $run = $agent->inspect();
            if ($run !== null) {
                if ($run->status === WorkflowStatus::Completed) $agent->acknowledge($run->runId);
                else $agent->discardRun($run->runId, $run->executionAttempt);
            }
            return ['answer' => $text !== '' ? $text . "\n\n（已停止生成）" : '已停止生成。',
                'tools' => $toolCalls, 'approval' => null, 'run_id' => $run?->runId ?? bin2hex(random_bytes(16)),
                'question' => $question, 'stopped' => true, 'task_ids' => $agent->submittedTaskIds()];
        }
        return $this->result($agent, $state, $question, $toolCalls);
    }

    private function result(BaseAgent $agent, AgentState $state, string $question, array $tools): array
    {
        $approval = $this->approval($agent);
        $answer = $approval !== null ? '请确认以下操作，批准后继续执行。' : trim((string)$state->getMessage()?->getContent());
        if ($answer === '') throw new \DomainException('模型没有返回文字，请稍后重试');
        return ['answer' => $answer, 'tools' => $tools, 'approval' => $approval, 'run_id' => $state->getRunId(), 'question' => $question, 'task_ids' => $agent->submittedTaskIds()];
    }
}

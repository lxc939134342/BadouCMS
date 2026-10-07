<?php

namespace app\common\agent\queue;

use app\common\agent\AgentContext;
use app\common\agent\model\AgentTask;
use think\facade\Log;

/**
 * Agent 任务服务
 * 提供任务提交、触发执行等统一接口
 */
class AgentTaskService
{
    private static array $pendingIds = [];
    private static bool $shutdownRegistered = false;
    /**
     * 提交任务到队列
     * 
     * @param string $type 任务类型，如：cms.translate
     * @param AgentContext $context Agent 上下文
     * @param array $payload 任务参数
     * @param int $priority 优先级 0-9，数字越大越优先
     * @return AgentTask
     */
    public function submit(string $type, AgentContext $context, array $payload, int $priority = 0): AgentTask
    {
        // 检查任务类型是否已注册
        if (!AgentTaskRegistry::has($type)) {
            throw new \DomainException("未知的任务类型：{$type}");
        }
        
        // 生成防重复提交的 key
        $requestKey = $this->generateRequestKey($type, $context, $payload);
        
        // 检查是否已存在相同任务
        $existing = AgentTask::where('request_key', $requestKey)->find();
        if ($existing) {
            $context->submittedTaskIds[] = (int)$existing->id;
            if ($existing->status === 'queued') $this->trigger($existing);
            return $existing;
        }
        
        // 创建任务
        $module = explode('.', $type)[0];
        $task = new AgentTask();
        
        try {
            $task->save([
                'type' => $type,
                'module' => $module,
                'tenant_id' => $context->tenantId,
                'admin_id' => $context->adminId,
                'chat_id' => $context->chatId,
                'request_key' => $requestKey,
                'status' => 'queued',
                'priority' => max(0, min(9, $priority)),
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'progress' => '{}',
                'result' => '{}',
                'error' => '',
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        } catch (\Throwable $e) {
            // 并发提交时可能已创建，再次检查
            $existing = AgentTask::where('request_key', $requestKey)->find();
            if ($existing) {
                $context->submittedTaskIds[] = (int)$existing->id;
                if ($existing->status === 'queued') $this->trigger($existing);
                return $existing;
            }
            throw $e;
        }
        
        // 尝试触发执行
        $this->trigger($task);
        $context->submittedTaskIds[] = (int)$task->id;
        
        return $task;
    }

    /**
     * 触发任务执行（支持 FPM 和 CLI）
     */
    public function trigger(AgentTask $task): void
    {
        // CLI 提交只入队，由独立 worker 消费；FPM 保持免部署兼容。
        if (!self::usesFpm() || $this->isCli() || !in_array($task->getPublicData()['status'], ['queued', 'interrupted'], true)) return;
        $id = (int)$task->id;
        self::$pendingIds[$id] = $id;
        if (self::$shutdownRegistered) return;
        self::$shutdownRegistered = true;
        // 等对话结果和消息记录保存完成，再结束响应并执行任务，释放会话锁供前端轮询。
        $this->defer(function (): void {
            $ids = self::$pendingIds;
            self::$pendingIds = [];
            self::$shutdownRegistered = false;
            $this->finishResponse();
            // 同一站点最多一个 FPM 后台消费者，避免多个长任务占满页面请求进程。
            if (!AgentTask::acquireLock(0)) return;
            try {
                $started = microtime(true);
                $seconds = max(1, (int)config('agent.queue.fpm_max_seconds', 60));
                $batches = max(1, (int)config('agent.queue.fpm_max_batches', 10));
                foreach ($ids as $taskId) {
                    $remaining = $seconds - (microtime(true) - $started);
                    if ($remaining <= 0) break;
                    try {
                        $this->executor()->execute($taskId, $batches, $remaining, true);
                    } catch (\Throwable $e) {
                        // 不清空游标；进程或连接异常后，后续请求仍可唤醒已批准的任务。
                        Log::error('Agent 响应后执行异常：任务ID={id}，类型={type}', ['id' => $taskId, 'type' => $e::class]);
                    }
                }
            } finally {
                AgentTask::releaseLock(0);
                Log::save();
            }
        });
    }

    public static function usesFpm(): bool
    {
        $mode = config('agent.queue.mode', 'fpm');
        if (!in_array($mode, ['fpm', 'cli'], true)) throw new \DomainException('Agent 队列执行模式无效');
        return $mode === 'fpm';
    }

    /** 唤醒已批准任务必须校验账号归属，浏览器不能提交执行参数。 */
    public function wake(AgentContext $context, int $id): void
    {
        $this->trigger(AgentTask::owned($id, $context->tenantId, $context->adminId));
    }

    protected function isCli(): bool { return PHP_SAPI === 'cli'; }
    protected function executor(): AgentTaskExecutor { return new AgentTaskExecutor(); }
    protected function defer(callable $callback): void { register_shutdown_function($callback); }

    protected function finishResponse(): void
    {
        ignore_user_abort(true);
        function_exists('set_time_limit') && @set_time_limit(0);
        app()->make('session')->save();
        if (function_exists('session_write_close')) @session_write_close();
        if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
        else {
            while (ob_get_level() > 0 && @ob_end_flush()) {}
            flush();
        }
    }

    /**
     * 生成请求唯一标识
     */
    private function generateRequestKey(string $type, AgentContext $context, array $payload): string
    {
        $data = [
            'tenant_id' => $context->tenantId,
            'admin_id' => $context->adminId,
            'chat_id' => $context->chatId,
            'type' => $type,
            'payload' => $payload,
        ];
        
        return hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
}

<?php

namespace app\common\agent\model;

use app\common\agent\AgentRunControl;
use think\Model;
use think\facade\Db;

/**
 * Agent 通用后台任务模型
 * 统一管理所有模块的 Agent 后台任务队列
 */
class AgentTask extends Model
{
    protected $name = 'agent_task';
    protected $autoWriteTimestamp = false;

    /**
     * 获取下一个待执行的任务ID
     */
    public static function fetchNextId(): ?int
    {
        $id = static::where(function ($query) {
                $query->where('status', 'queued')->whereOr(function ($query) {
                    $query->where('status', 'running')->where('heartbeat_at', '<', time() - 600);
                });
            })
            ->order('priority', 'desc')
            ->order('id', 'asc')
            ->value('id');
            
        return $id ? (int)$id : null;
    }

    /**
     * 认领任务（将状态改为 running）
     */
    public static function claim(int $id): ?self
    {
        $affected = static::where('id', $id)
            ->where('status', 'queued')
            ->update([
                'status' => 'running',
                'started_at' => time(),
                'updated_at' => time(),
                'heartbeat_at' => time(),
            ]);
            
        if ($affected !== 1) {
            return null;
        }
        
        return static::find($id);
    }

    /**
     * 获取任务锁（使用 MySQL GET_LOCK）
     */
    public static function acquireLock(int $id): bool
    {
        $lockName = self::lockName($id);
        $result = Db::query('SELECT GET_LOCK(?, 0) AS acquired', [$lockName], true);
        return isset($result[0]['acquired']) && (int)$result[0]['acquired'] === 1;
    }

    /**
     * 释放任务锁
     */
    public static function releaseLock(int $id): void
    {
        $lockName = self::lockName($id);
        Db::query('SELECT RELEASE_LOCK(?)', [$lockName], true);
    }

    /**
     * 生成锁名称
     */
    private static function lockName(int $id): string
    {
        $database = config('database.connections.' . config('database.default') . '.database', '');
        $prefix = config('database.connections.' . config('database.default') . '.prefix', '');
        return 'agent_task_' . substr(hash('sha256', $database . ':' . $prefix . ':' . $id), 0, 48);
    }

    /**
     * 更新任务心跳
     */
    public function heartbeat(): void
    {
        static::where('id', $this->id)
            ->where('status', 'running')
            ->update(['heartbeat_at' => time(), 'updated_at' => time()]);
    }

    /**
     * 更新任务进度
     */
    public function updateProgress(array $progress): void
    {
        $data = [
            'progress' => json_encode($progress, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'heartbeat_at' => time(),
            'updated_at' => time(),
        ];
        
        if (static::where('id', $this->id)->where('status', 'running')->update($data) === false) {
            throw new \DomainException('任务进度更新失败');
        }
        
        $this->progress = $data['progress'];
    }

    /**
     * 标记任务为已完成
     */
    public function markCompleted(array $result = []): void
    {
        static::where('id', $this->id)->where('status', 'running')->update([
            'status' => 'completed',
            'result' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'error' => '',
            'updated_at' => time(),
        ]);
    }

    /**
     * 标记任务为失败
     */
    public function markFailed(string $error): void
    {
        static::where('id', $this->id)->where('status', 'running')->update([
            'status' => 'failed',
            'error' => mb_substr($error, 0, 500),
            'updated_at' => time(),
        ]);
    }

    /**
     * 标记任务为已取消
     */
    public function markCancelled(string $reason = ''): void
    {
        static::where('id', $this->id)->where('status', 'running')->update([
            'status' => 'cancelled',
            'error' => mb_substr($reason, 0, 500),
            'updated_at' => time(),
        ]);
    }

    /**
     * 获取任务参数
     */
    public function getPayload(): array
    {
        return json_decode((string)$this->payload, true, 512, JSON_THROW_ON_ERROR) ?: [];
    }

    /**
     * 获取任务进度
     */
    public function getProgress(): array
    {
        return json_decode((string)$this->progress, true, 512, JSON_THROW_ON_ERROR) ?: [];
    }

    /**
     * 获取任务结果
     */
    public function getResult(): array
    {
        return json_decode((string)$this->result, true, 512, JSON_THROW_ON_ERROR) ?: [];
    }

    public function control(): AgentRunControl
    {
        return new AgentRunControl((int)$this->tenant_id, (int)$this->admin_id, md5('agent-task:' . $this->id . ':' . $this->request_key));
    }

    /** 调用者持有任务锁时，将已保存的进度交还给下一次执行。 */
    public function yieldToQueue(): void
    {
        static::where('id', $this->id)->where('status', 'running')->update([
            'status' => 'queued', 'heartbeat_at' => time(), 'updated_at' => time(),
        ]);
    }

    public static function owned(int $id, int $tenantId, int $adminId): self
    {
        $task = static::find($id);
        if (!$task || (int)$task->tenant_id !== $tenantId || (int)$task->admin_id !== $adminId) throw new \DomainException('任务不存在或无权访问');
        return $task;
    }

    /** 自动恢复仅允许处理器声明可续跑的任务，且须先持有执行锁。 */
    public function recoverInterrupted(): void
    {
        static::where('id', $this->id)->where('status', 'running')->where('heartbeat_at', '<', time() - 600)->update([
            'status' => 'queued', 'error' => '', 'updated_at' => time(),
        ]);
    }

    /**
     * 获取公开数据（用于 API 返回）
     */
    public function getPublicData(): array
    {
        $status = (string)$this->status;
        
        // 心跳超时判断（10分钟无心跳视为中断）
        if ($status === 'running' && time() - (int)$this->heartbeat_at > 600) {
            $status = 'interrupted';
        }
        
        return [
            'id' => (int)$this->id,
            'type' => (string)$this->type,
            'module' => (string)$this->module,
            'status' => $status,
            'priority' => (int)$this->priority,
            'progress' => $this->getProgress(),
            'result' => $this->getResult(),
            'error' => (string)$this->error,
            'created_at' => (int)$this->created_at,
            'updated_at' => (int)$this->updated_at,
            'started_at' => (int)$this->started_at,
            'display' => $this->type === 'cms.translate' ? $this->languageDisplay() : [],
        ];
    }

    /** 只向页面提供展示字段，不暴露任务原始参数和快照。 */
    private function languageDisplay(): array
    {
        $plan = $this->getPayload()['plan'] ?? [];
        $options = $plan['options'] ?? [];
        return [
            'source' => (string)($plan['source_name'] ?? $options['source'] ?? ''),
            'target' => (string)($plan['target_name'] ?? $options['target'] ?? ''),
            'kind' => (string)($options['kind'] ?? 'categories'),
            'copy_first' => (bool)($options['copy_first'] ?? false),
            'total' => (int)($plan['total'] ?? 0),
        ];
    }

    /**
     * 查询会话的任务列表
     */
    public static function listForChat(int $tenantId, int $adminId, int $chatId, int $limit = 20): array
    {
        $list = static::where('tenant_id', $tenantId)
            ->where('admin_id', $adminId)
            ->where('chat_id', $chatId)
            ->order('id', 'desc')
            ->limit($limit)
            ->select();
            
        return array_map(fn($task) => $task->getPublicData(), $list->all());
    }

    /**
     * 重试任务
     */
    public static function retry(int $id, int $tenantId, int $adminId): self
    {
        $task = static::find($id);
        if (!$task) {
            throw new \DomainException('任务不存在');
        }
        
        if ((int)$task->tenant_id !== $tenantId || (int)$task->admin_id !== $adminId) {
            throw new \DomainException('无权操作此任务');
        }
        
        // 只有失败或心跳超时的任务可以重试
        $canRetry = in_array($task->status, ['failed', 'running'], true);
        if ($task->status === 'running') {
            $canRetry = time() - (int)$task->heartbeat_at > 600;
        }
        
        if (!$canRetry) {
            throw new \DomainException('当前任务无需重试');
        }
        
        // 检查任务是否仍被锁定
        if (!self::acquireLock($id)) {
            throw new \DomainException('任务仍在处理，不能重复启动');
        }
        
        try {
            static::where('id', $id)->update([
                'status' => 'queued',
                'error' => '',
                'updated_at' => time(),
            ]);
            
            return static::find($id);
        } finally {
            self::releaseLock($id);
        }
    }

    /**
     * 取消任务
     */
    public static function cancel(int $id, int $tenantId, int $adminId): void
    {
        $task = static::find($id);
        if (!$task) {
            throw new \DomainException('任务不存在');
        }
        
        if ((int)$task->tenant_id !== $tenantId || (int)$task->admin_id !== $adminId) {
            throw new \DomainException('无权操作此任务');
        }
        
        if (in_array($task->status, ['queued', 'running'], true)) {
            $task->control()->stop();
            static::where('id', $id)->whereIn('status', ['queued', 'running'])->update([
                'status' => 'cancelled',
                'error' => '任务已取消',
                'updated_at' => time(),
            ]);
        }
    }
}

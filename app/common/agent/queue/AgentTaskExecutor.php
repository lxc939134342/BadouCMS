<?php

namespace app\common\agent\queue;

use app\common\agent\model\AgentTask;
use app\common\agent\AgentContext;
use app\common\library\AdminAuth;
use app\common\agent\{AgentRunControl, AgentToolExecutor};
use think\facade\Log;

/**
 * Agent 任务执行器
 * 负责实际执行队列中的任务
 */
class AgentTaskExecutor
{
    /**
     * 执行指定任务
     * 
     * @param int $id 任务ID
     * @return array 执行结果
     */
    public function execute(int $id, int $maxBatches = 0, float $maxSeconds = 0, bool $recoverInterrupted = false): array
    {
        // 获取分布式锁
        if (!AgentTask::acquireLock($id)) {
            $task = AgentTask::find($id);
            return $task ? $task->getPublicData() : ['status' => 'not_found'];
        }
        
        try {
            return $this->executeLocked($id, $maxBatches, $maxSeconds, $recoverInterrupted);
        } finally {
            AgentTask::releaseLock($id);
        }
    }

    /**
     * 在锁保护下执行任务
     */
    private function executeLocked(int $id, int $maxBatches, float $maxSeconds, bool $recoverInterrupted): array
    {
        if ($recoverInterrupted) {
            $interrupted = AgentTask::find($id);
            if ($interrupted && $interrupted->getPublicData()['status'] === 'interrupted') {
                $handler = AgentTaskRegistry::make($interrupted->type);
                if ($handler && method_exists($handler, 'canResume') && $handler->canResume($interrupted)) $interrupted->recoverInterrupted();
                else $interrupted->markFailed('任务中断，当前阶段无法自动续跑，请重新预览并提交任务。');
            }
        }
        // 认领任务（将状态改为 running）
        $task = AgentTask::claim($id);
        if (!$task) {
            $existing = AgentTask::find($id);
            return $existing ? $existing->getPublicData() : ['status' => 'not_found'];
        }
        
        try {
            // 获取任务处理器
            $handler = AgentTaskRegistry::make($task->type);
            if (!$handler) {
                throw new \DomainException("任务类型 {$task->type} 未注册处理器");
            }
            
            // 构建 Agent 上下文
            $context = $this->buildContext($task);
            
            // 持续执行直到完成
            $batchCount = 0;
            $started = microtime(true);
            
            while ($batchCount < 1000) {
                $task = AgentTask::find($id);
                if ($task->status !== 'running') break;
                $result = $handler->process($task, $context);
                $batchCount++;
                
                // 检查是否继续
                $shouldContinue = $result['continue'] ?? false;
                if (!$shouldContinue) {
                    break;
                }

                // 只在进度已保存的批次边界让出 FPM，不能把未完成任务标成 completed。
                if (($maxBatches > 0 && $batchCount >= $maxBatches) || ($maxSeconds > 0 && microtime(true) - $started >= $maxSeconds)) {
                    $task->yieldToQueue();
                    break;
                }
                
                // 重新构建上下文（刷新权限）
                $context = $this->buildContext($task);
            }
            
            if ($batchCount >= 1000 && $shouldContinue) {
                throw new \DomainException('任务执行批次过多，已停止以避免死循环');
            }
            
            // 标记完成
            $task = AgentTask::find($id);
            if ($task->status === 'running') {
                $task->markCompleted(['batches' => $batchCount]);
            }
            
        } catch (\Throwable $e) {
            Log::error('Agent 任务执行失败：任务ID={id}，类型={type}，异常={exception}', [
                'id' => $id,
                'type' => $task->type ?? 'unknown',
                'exception' => $e->getMessage(),
            ]);
            
            if (AgentTask::find($id)->status === 'running') {
                $task->markFailed(\app\common\ai\AiGateway::explain($e)->getMessage());
            }
        }
        
        return AgentTask::find($id)->getPublicData();
    }

    /**
     * 构建 Agent 上下文
     */
    protected function buildContext(AgentTask $task): AgentContext
    {
        // 加载管理员信息
        $adminModel = '\\modules\\' . $task->module . '\\agent\\model\\TaskAdmin';
        
        // 如果模块没有专门的 TaskAdmin，使用通用的
        if (!class_exists($adminModel)) {
            $adminModel = '\\app\\common\\agent\\model\\TaskAdmin';
        }
        
        $admin = $adminModel::find((int)$task->admin_id);
        if (!$admin || $admin->status !== 'normal') {
            throw new \DomainException('提交任务的账号已停用');
        }
        
        // 获取最新权限
        $rules = null;
        if (method_exists($admin, 'freshRules')) {
            $rules = $admin->freshRules();
        }
        
        // 构建权限对象
        $auth = new class($admin->toArray(), $rules) extends AdminAuth {
            public function __construct(private array $admin, private ?array $freshRules) { 
                parent::__construct(); 
            }
            
            public function __get($name) { 
                return $this->admin[$name] ?? null; 
            }
            
            public function getUserInfo($uid = null) { 
                if ($uid === null || (int)$uid === (int)$this->admin['id']) {
                    return $this->admin;
                }
                return parent::getUserInfo($uid);
            }
            
            public function getRuleList($uid = null) { 
                if ($uid === null || (int)$uid === (int)$this->admin['id']) {
                    return $this->freshRules ?? parent::getRuleList((int)$this->admin['id']);
                }
                return [];
            }
        };
        
        // 构建停止控制
        $control = $task->control();
        
        // 创建上下文
        $context = new AgentContext(
            $auth,
            (int)$task->admin_id,
            (int)$task->tenant_id,
            (int)$task->chat_id,
            control: $control
        );
        
        // 验证基本权限
        $executor = new AgentToolExecutor();
        if (!$executor->allowed(['module' => $task->module, 'permission' => 'agent/send'], $context)) {
            throw new \DomainException('模块已停用或账号权限已变化');
        }
        
        return $context;
    }
}

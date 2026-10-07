<?php

namespace app\common\agent\queue;

use app\common\agent\{AgentContext, AgentStoppedException};
use app\common\agent\model\AgentTask;

/**
 * Agent 任务处理器抽象基类
 * 提供通用的任务处理能力，子类只需实现核心业务逻辑
 */
abstract class AbstractAgentTask implements AgentTaskInterface
{
    /**
     * 处理任务的一个批次（由子类实现）
     * 
     * @param AgentTask $task 任务实例
     * @param AgentContext $context Agent 上下文
     * @return array ['continue' => bool, 'progress' => array, 'message' => string]
     */
    abstract protected function processBatch(AgentTask $task, AgentContext $context): array;
    
    /**
     * 获取任务名称（由子类实现）
     */
    abstract protected function getTaskName(): string;

    /** 未声明幂等续跑的处理器不自动重启，避免重复外部操作。 */
    public function canResume(AgentTask $task): bool
    {
        return false;
    }

    /**
     * 统一的任务执行入口（处理心跳、异常、进度更新）
     */
    public function process(AgentTask $task, AgentContext $context): array
    {
        // 更新心跳
        $task->heartbeat();
        
        try {
            // 检查是否被停止
            $context->control?->check(true);
            
            // 执行业务逻辑
            $result = $this->processBatch($task, $context);
            
            // 更新进度
            if (isset($result['progress']) && is_array($result['progress'])) {
                $task->updateProgress($result['progress']);
            }
            
            return $result;
            
        } catch (AgentStoppedException $e) {
            $task->markCancelled('任务已停止');
            return ['continue' => false, 'message' => '任务已停止'];
        } catch (\Throwable $e) {
            $task->markFailed(\app\common\ai\AiGateway::explain($e)->getMessage());
            throw $e;
        }
    }

    /**
     * 验证必需字段
     */
    protected function fields(array $input, array $required): void
    {
        foreach ($required as $field) {
            if (!isset($input[$field])) {
                throw new \DomainException("缺少必需参数：{$field}");
            }
        }
    }

    /**
     * 验证文本字段
     */
    protected function text(string $value, string $label, int $maxLength = 100, bool $required = false): string
    {
        $value = trim($value);
        
        if ($required && $value === '') {
            throw new \DomainException("{$label}不能为空");
        }
        
        if (mb_strlen($value) > $maxLength) {
            throw new \DomainException("{$label}长度不能超过 {$maxLength} 个字符");
        }
        
        return $value;
    }

    /**
     * 验证权限
     */
    protected function permit(AgentContext $context, string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            if (!$context->auth->check($permission)) {
                throw new \DomainException("无权执行操作：{$permission}");
            }
        }
    }
}

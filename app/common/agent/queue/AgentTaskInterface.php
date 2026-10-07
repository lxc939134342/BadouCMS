<?php

namespace app\common\agent\queue;

use app\common\agent\AgentContext;
use app\common\agent\model\AgentTask;

/**
 * Agent 任务处理器接口
 * 所有 Agent 后台任务处理器必须实现此接口
 */
interface AgentTaskInterface
{
    /**
     * 执行任务的一个批次
     * 
     * @param AgentTask $task 任务实例
     * @param AgentContext $context Agent 上下文
     * @return array ['continue' => bool, 'progress' => array, 'message' => string]
     *               continue: 是否还有后续批次需要继续执行
     *               progress: 进度数据，如 {processed: 10, total: 100, saved: 8, skipped: 2}
     *               message: 本批次执行说明
     */
    public function process(AgentTask $task, AgentContext $context): array;
    
    /**
     * 任务审批前的预览数据
     * 
     * @param AgentContext $context Agent 上下文
     * @param array $input 用户输入参数
     * @return array 预览数据，包含 snapshot 用于防止数据变化
     */
    public function preview(AgentContext $context, array $input): array;
    
    /**
     * 审批界面显示数据
     * 
     * @param AgentContext $context Agent 上下文
     * @param array $input 用户输入参数（包含 snapshot）
     * @return array 审批界面需要的数据，包含 type、title、notes 等
     */
    public function approvalDisplay(AgentContext $context, array $input): array;
}

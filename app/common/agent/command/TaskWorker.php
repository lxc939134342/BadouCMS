<?php

namespace app\common\agent\command;

use app\common\agent\model\AgentTask;
use app\common\agent\queue\AgentTaskExecutor;
use think\console\{Command, Input, Output};
use think\console\input\Option;
use think\facade\{Cache, Db};

/**
 * Agent 任务队列 Worker
 * 支持三种执行模式：指定任务、守护进程、单次执行（Cron）
 */
class TaskWorker extends Command
{
    protected function configure()
    {
        $this->setName('agent:task')
            ->setDescription('执行 Agent 后台任务队列')
            ->addOption('id', null, Option::VALUE_REQUIRED, '执行指定任务ID')
            ->addOption('watch', null, Option::VALUE_NONE, '持续消费队列（守护进程模式）')
            ->addOption('once', null, Option::VALUE_NONE, '处理一个任务后退出（Cron 模式）');
    }

    protected function execute(Input $input, Output $output)
    {
        $this->prepareRuntime();
        
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }
        
        $executor = new AgentTaskExecutor();
        
        // 模式 1：执行指定任务
        if ($taskId = $input->getOption('id')) {
            $result = $executor->execute((int)$taskId, recoverInterrupted: true);
            $output->writeln('任务 ' . $taskId . '：' . ($result['status'] ?? 'unknown'));
            return;
        }
        
        // 模式 2：守护进程模式（Supervisor）
        if ($input->getOption('watch')) {
            $output->writeln('守护进程模式启动，等待任务...');
            
            while (true) {
                $taskId = AgentTask::fetchNextId();
                
                if ($taskId) {
                    $output->writeln('[' . date('Y-m-d H:i:s') . '] 处理任务 ' . $taskId);
                    $result = $executor->execute($taskId, recoverInterrupted: true);
                    $output->writeln('[' . date('Y-m-d H:i:s') . '] 任务 ' . $taskId . ' 状态：' . ($result['status'] ?? 'unknown'));
                    if (in_array($result['status'] ?? '', ['queued', 'interrupted'], true)) usleep(200000);
                } else {
                    sleep(2);
                }
            }
            
            return;
        }
        
        // 模式 3：单次执行模式（Cron）
        if ($input->getOption('once')) {
            $taskId = AgentTask::fetchNextId();
            
            if ($taskId) {
                $result = $executor->execute($taskId, recoverInterrupted: true);
                $output->writeln('已处理任务 ' . $taskId . '，状态：' . ($result['status'] ?? 'unknown'));
            } else {
                $output->writeln('队列为空');
            }
            
            return;
        }
        
        $output->error('请指定执行模式：--id <任务ID>、--watch 或 --once');
    }

    /**
     * 准备运行时环境
     * 确保 Worker 与后台共享缓存和数据库配置
     */
    protected function prepareRuntime(): void
    {
        // 默认文件缓存按应用目录隔离，worker 必须与后台共享 AI 账号、停止信号等缓存
        $runtime = rtrim($this->app->getRuntimePath(), '/\\');
        if (basename($runtime) !== 'admin') {
            $this->app->setRuntimePath($runtime . '/admin/');
        }
        
        // 重置缓存驱动
        Cache::forgetDriver();
        Db::setCache(Cache::store());
    }
}

<?php

namespace app\common\agent\command;

use think\console\{Command, Input, Output};
use think\facade\Db;
use app\common\agent\model\AgentTask;
use app\common\agent\queue\AgentTaskRegistry;

/**
 * Agent 任务队列测试命令
 */
class TestQueue extends Command
{
    protected function configure()
    {
        $this->setName('agent:test')
            ->setDescription('测试 Agent 任务队列系统');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("=== Agent 任务队列系统测试 ===\n");

        // 1. 检查数据库连接
        $output->writeln("[1] 检查数据库连接...");
        try {
            Db::query('SELECT 1');
            $output->writeln("✓ 数据库连接正常\n");
        } catch (\Throwable $e) {
            $output->error("✗ 数据库连接失败：{$e->getMessage()}");
            return 1;
        }

        // 2. 仅检查安装或升级是否已创建任务表，诊断过程不修改数据结构。
        $output->writeln("[2] 检查任务表...");
        try {
            AgentTask::limit(1)->select();
            $output->writeln("✓ 任务表可访问\n");
        } catch (\Throwable $e) {
            $output->error("✗ 任务表不可访问，请先通过框架升级插件执行 Agent 升级 SQL：{$e->getMessage()}");
            return 1;
        }

        // 3. 检查已注册的任务类型
        $output->writeln("[3] 检查已注册的任务类型...");
        $handlers = AgentTaskRegistry::all();
        if (empty($handlers)) {
            $output->warning("⚠ 尚未注册任何任务处理器");
            $output->writeln("提示：请在模块的 task.php 中调用 AgentTaskRegistry::register()\n");
        } else {
            $output->writeln("已注册的任务类型：");
            foreach ($handlers as $type => $handler) {
                $output->writeln("  - {$type} => {$handler}");
            }
            $output->writeln("");
        }

        // 4. 测试任务锁
        $output->writeln("[4] 测试任务锁...");
        try {
            $testId = 999999;
            
            if (AgentTask::acquireLock($testId)) {
                $output->writeln("✓ 获取锁成功");
                AgentTask::releaseLock($testId);
                $output->writeln("✓ 释放锁成功\n");
            } else {
                $output->writeln("✗ 获取锁失败\n");
            }
        } catch (\Throwable $e) {
            $output->error("✗ 测试锁失败：{$e->getMessage()}\n");
        }

        // 5. 查询任务队列
        $output->writeln("[5] 查询任务队列...");
        try {
            $total = AgentTask::count();
            $queued = AgentTask::where('status', 'queued')->count();
            $running = AgentTask::where('status', 'running')->count();
            $completed = AgentTask::where('status', 'completed')->count();
            $failed = AgentTask::where('status', 'failed')->count();
            
            $output->writeln("任务统计：");
            $output->writeln("  - 总计：{$total} 个");
            $output->writeln("  - 队列中：{$queued} 个");
            $output->writeln("  - 执行中：{$running} 个");
            $output->writeln("  - 已完成：{$completed} 个");
            $output->writeln("  - 失败：{$failed} 个\n");
        } catch (\Throwable $e) {
            $output->error("✗ 查询失败：{$e->getMessage()}\n");
        }

        // 6. 检查心跳超时
        $output->writeln("[6] 检查心跳超时的任务...");
        try {
            $timeout = time() - 600;
            $interrupted = AgentTask::where('status', 'running')
                ->where('heartbeat_at', '<', $timeout)
                ->count();
            
            if ($interrupted > 0) {
                $output->warning("⚠ 发现 {$interrupted} 个任务心跳超时（可能已中断）");
                $output->writeln("提示：可以使用 AgentTask::retry() 重试这些任务\n");
            } else {
                $output->writeln("✓ 没有心跳超时的任务\n");
            }
        } catch (\Throwable $e) {
            $output->error("✗ 检查失败：{$e->getMessage()}\n");
        }

        // 7. 检查 CLI 命令
        $output->writeln("[7] 检查 CLI 命令...");
        $thinkPath = root_path() . 'think';
        if (file_exists($thinkPath)) {
            $output->writeln("✓ think 命令文件存在");
            $output->writeln("可用命令：");
            $output->writeln("  php think agent:task --once          # 处理一个任务");
            $output->writeln("  php think agent:task --watch         # 守护进程模式");
            $output->writeln("  php think agent:task --id=<任务ID>  # 执行指定任务\n");
        } else {
            $output->error("✗ think 命令文件不存在\n");
        }

        // 8. 检查实际使用的后台任务接续入口，而非旧的本机 HTTP 触发控制器。
        $output->writeln("[8] 检查 FPM 任务接续入口...");
        if (is_subclass_of(\app\admin\controller\Agent::class, \app\common\controller\Backend::class)
            && method_exists(\app\admin\controller\Agent::class, 'send')) {
            $output->writeln("✓ Agent 后台控制器可加载");
            $output->writeln("接续入口：POST agent/send，action=task_wake，task_id=<任务ID>（需后台登录、权限及 CSRF 令牌）\n");
        } else {
            $output->error("✗ Agent 后台任务接续入口不可用\n");
        }

        // 9. 部署建议
        $output->writeln("[9] 部署建议");
        $output->writeln("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $output->writeln("小规模（<1000 任务/天）：");
        $output->writeln("  默认 FPM 模式：响应结束后执行任务，页面自动接续剩余批次\n");
        $output->writeln("  关闭页面后若要持续消费队列，请配置 Cron 或 CLI Worker\n");
        
        $output->writeln("中等规模（1000-10000 任务/天）：");
        $output->writeln("  添加 Cron 定时任务：");
        $output->writeln("  * * * * * cd " . root_path() . " && php think agent:task --once\n");
        
        $output->writeln("大规模（>10000 任务/天）：");
        $output->writeln("  使用 Supervisor 守护进程：");
        $output->writeln("  [program:agent-worker]");
        $output->writeln("  command=php " . root_path() . "think agent:task --watch");
        $output->writeln("  numprocs=3");
        $output->writeln("  autostart=true");
        $output->writeln("  autorestart=true\n");
        
        $output->writeln("=== 测试完成 ===");
        return 0;
    }
}

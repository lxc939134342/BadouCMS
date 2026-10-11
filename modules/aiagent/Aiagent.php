<?php

namespace modules\aiagent;

use app\common\agent\command\TaskWorker;
use modules\aiagent\library\Runtime;
use modules\aiagent\model\Installation;
use think\Console;
use think\facade\{Event, View};

class Aiagent
{
    public function AppInit(): bool
    {
        Runtime::boot();
        Event::listen('admin_body_end', static function (array $params): string {
            $auth = $params['auth'] ?? null;
            if (!Runtime::enabled() || !$auth || !$auth->check('agent/index')) {
                return '';
            }
            return View::fetch(__DIR__ . '/view/launcher.html', ['auth' => $auth]);
        });
        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            Console::starting(static function (Console $console): void {
                $console->addCommands([TaskWorker::class]);
            });
        }
        return true;
    }

    public function install(): bool
    {
        Runtime::boot();
        Installation::installSchema();
        Installation::ensureMenu();
        return true;
    }

    public function enable(): bool
    {
        $this->install();
        Installation::setMenuEnabled(true);
        return true;
    }

    public function disable(): bool
    {
        Installation::setMenuEnabled(false);
        return true;
    }

    public function uninstall(): bool
    {
        // 卸载只移除入口；会话、审批、任务与代码备份保留，重新安装可继续使用。
        Installation::removeMenu();
        return true;
    }

    public function upgrade(): bool
    {
        return $this->install();
    }
}

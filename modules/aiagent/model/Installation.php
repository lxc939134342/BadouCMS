<?php

namespace modules\aiagent\model;

use app\admin\model\AdminRule;
use app\common\library\Menu;
use badou\Server;
use think\facade\Cache;

class Installation
{
    public static function installSchema(): void
    {
        Server::importsql('aiagent', null, true);
    }

    public static function ensureMenu(): void
    {
        // 沿用原有节点名称与 ID，避免迁移时丢失已分配的角色权限。
        if (!AdminRule::getByName('agent')) {
            Menu::create([['name' => 'agent', 'title' => 'AI 助手', 'icon' => 'fa fa-magic',
                'url' => 'agent/index', 'type' => 1, 'ismenu' => 1, 'weigh' => 100]]);
        }
        $actions = [
            'index' => ['查看', 1], 'sessions' => ['查看会话', 1], 'history' => ['查看消息', 1],
            'send' => ['发送消息', 2], 'patches' => ['查看代码改动', 1],
            'approve' => ['批准代码改动', 2], 'reject' => ['拒绝代码改动', 2],
            'rollback' => ['回退代码改动', 2], 'delete' => ['删除会话', 2],
        ];
        $missing = [];
        foreach ($actions as $action => [$title, $type]) {
            if (!AdminRule::getByName('agent/' . $action)) {
                $missing[] = ['name' => 'agent/' . $action, 'title' => $title, 'type' => $type, 'ismenu' => 0];
            }
        }
        if ($missing) Menu::create($missing, 'agent');
        Cache::delete('__menu__');
    }

    public static function setMenuEnabled(bool $enabled): void
    {
        $enabled ? Menu::enable('agent') : Menu::disable('agent');
        Cache::delete('__menu__');
    }

    public static function removeMenu(): void
    {
        Menu::delete('agent');
        Cache::delete('__menu__');
    }
}

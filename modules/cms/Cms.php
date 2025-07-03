<?php

namespace modules\cms;

use app\common\library\Menu;

class Cms
{
    public function AppInit()
    {
        include_once __DIR__ . '/common.php';
        bind('think\Paginator', 'modules\cms\library\Bootstrap');
    }

    public function enable()
    {
        Menu::enable('cms');
        return true;
    }

    public function disable()
    {
        Menu::disable('cms');
        return true;
    }

    public function install()
    {
        $menu = include_once __DIR__ . '/menu.php';
        Menu::create($menu);
        return true;
    }

    public function uninstall()
    {
        Menu::delete('cms');
        return true;
    }

}

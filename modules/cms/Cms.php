<?php

namespace modules\cms;

class Cms
{
    public function AppInit()
    {
        include_once __DIR__ . '/common.php';
        bind('think\Paginator', 'modules\cms\library\Bootstrap');
        return [];
    }

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }
}

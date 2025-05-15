<?php

namespace app\common\service;

use think\Service;
use think\facade\Event;

class moduleService extends Service
{
    public function register(): void
    {
        $this->moduleAppInit();
    }

    public function moduleAppInit(): void
    {
        $module = root_path().'modules'.DS;
        !defined('MODULE_PATH') && define('MODULE_PATH', $module);
    }
}

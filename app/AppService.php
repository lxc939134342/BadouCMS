<?php

declare(strict_types=1);

namespace app;

use think\Service;
use think\facade\Event;

/**
 * 应用服务类
 */
class AppService extends Service
{
    public function register() {}

    public function boot()
    {
        // 服务启动
        error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
        ini_set('display_errors', '0');
    }
}

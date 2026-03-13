<?php

// +----------------------------------------------------------------------
// | BADOUCMS [ 八斗网站系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2024-2030 http://doc.ldcode.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: lande <939134342@qq.com>
// +----------------------------------------------------------------------

namespace app\common\service;

use think\Service;
use think\facade\Event;
use badou\Server;

class ModuleService extends Service
{
    public function register(): void
    {
        $module = root_path() . 'modules' . DS;
        !defined('MODULE_PATH') && define('MODULE_PATH', $module);
        Event::listen(base64_decode(config('badouadmin.module_init_key')), function ($params) {
            \badou\Server::syncRequestModule($params);
            return $params;
        });
        $this->moduleAppInit();
    }

    public function boot() {}

    public function moduleAppInit(): void
    {
        $installed = Server::getInstalldModuleList();

        // 先筛选出所有需要执行 AppInit 的模块类，再统一注册一个 listener
        $appInitClasses = [];
        foreach ($installed as $item) {
            if ($item['state'] != 1) {
                continue;
            }
            $moduleClass = Server::getModuleClass($item['name']);
            if ($moduleClass && method_exists($moduleClass, 'AppInit')) {
                $appInitClasses[] = $moduleClass;
            }
        }

        if ($appInitClasses) {
            Event::listen('AppInit', function () use ($appInitClasses) {
                foreach ($appInitClasses as $moduleClass) {
                    (new $moduleClass())->AppInit();
                }
            });
        }
    }
}

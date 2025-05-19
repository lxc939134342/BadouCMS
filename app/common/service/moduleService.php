<?php

namespace app\common\service;

use think\Service;
use think\facade\Event;
use badou\Server;

class ModuleService extends Service
{
    public function register(): void
    {
        $module = root_path().'modules'.DS;
        !defined('MODULE_PATH') && define('MODULE_PATH', $module);
        $this->moduleAppInit();
    }

    public function moduleAppInit(): void
    {
        $installed = Server::getInstalldModuleList();
        foreach ($installed as $item) {
            if ($item['state'] != 1) {
                continue;
            }
            $moduleClass = Server::getModuleClass($item['name']);
            if (class_exists($moduleClass)) {
                if (method_exists($moduleClass, 'AppInit')) {
                    Event::listen('AppInit', function () use ($moduleClass) {
                        $handle = new $moduleClass();
                        $handle->AppInit();
                    });
                }
            }
        }
    }
}

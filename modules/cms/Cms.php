<?php

namespace modules\cms;

use think\facade\Event;
use think\facade\Route;
use app\common\library\Menu;
use app\admin\model\Config as ConfigModel;

class Cms
{
    public function AppInit()
    {
        include_once __DIR__ . '/common.php';
        bind('think\Paginator', 'modules\cms\library\Bootstrap');
        Event::listen('cms_route_before', function () {
            Route::rule('index/:action', 'index/:action');
        });
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

        $config = include_once __DIR__ . '/config.php';
        $configModel = new ConfigModel();
        $configModel->setGroup('cms', 'CMS配置');
        $configModel->setSysConfig($config, false);
        $configModel::clearCache();

        return true;
    }

    public function upgrade()
    {
        $config = include_once __DIR__ . '/config.php';
        $configModel = new ConfigModel();
        $configModel->setGroup('cms', 'CMS配置');
        $configModel->setSysConfig($config, false);
        $configModel::clearCache();

        return true;
    }

    public function uninstall()
    {
        Menu::delete('cms');

        $configModel = new ConfigModel();
        $configModel->delGroup('cms');
        $configModel->where('group', 'cms')->delete();
        $configModel::clearCache();

        return true;
    }
}

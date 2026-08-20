<?php

namespace modules\cms;

use think\facade\Event;
use think\facade\Route;
use app\common\library\Menu;
use app\admin\model\Config as ConfigModel;
use modules\cms\library\RouteRegistry;

class Cms
{
    public function AppInit()
    {
        include_once __DIR__ . '/common.php';
        bind('think\Paginator', 'modules\cms\library\Bootstrap');
        Event::listen('cms_route_before', function () {
            Route::rule('index/:action', 'index/:action');
            foreach (RouteRegistry::all() as $rule => $target) {
                Route::rule($rule, $target);
            }
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
        // 仅补充本版本新增的菜单，避免升级时重复创建既有 CMS 菜单。
        Menu::create([[
            'name' => 'cms.route',
            'type' => '1',
            'title' => '前台扩展路由',
            'icon' => 'fa fa-link',
            'ismenu' => 1,
            'sublist' => [[
                'name' => 'cms.route/index',
                'type' => '1',
                'title' => '查看',
                'icon' => 'fa fa-circle-o',
                'ismenu' => 0,
            ]],
        ]], 'cms');

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

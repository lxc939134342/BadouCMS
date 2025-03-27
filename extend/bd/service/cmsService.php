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

namespace bd\service;

use think\Service;
use think\facade\Config;

class cmsService extends Service
{
    // 注册服务
    public function register(): void
    {
        Config::load(root_path() . 'extend/bd/config/badoucms.php', 'badoucms');
        include root_path() . 'extend/bd/common.php';

        // 如果修改了index.php入口地址，则需要手动修改cdnurl的值
        $url = preg_replace("/\/(\w+)\.php$/i", '', request()->root());
        // 如果未设置__CDN__则自动匹配得出
        if (!Config::get('tpl_replace_string.__CDN__')) {
            Config::set(['tpl_replace_string.__CDN__' => $url]);
        }
        // 如果未设置__PUBLIC__则自动匹配得出
        if (!Config::get('tpl_replace_string.__PUBLIC__')) {
            Config::set(['tpl_replace_string.__PUBLIC__' => $url . '/']);
        }
        // 如果未设置__ROOT__则自动匹配得出
        if (!Config::get('tpl_replace_string.__ROOT__')) {
            Config::set(['tpl_replace_string.__ROOT__' => preg_replace("/\/public\/$/", '', $url . '/')]);
        }
        $this->app->bind('cms_route', 'bd\CmsRoute');

    }

    public function boot(): void
    {

    }
}

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

namespace modules\upgrade;

use think\facade\Event;

class Upgrade
{
    public function appInit()
    {
        // admin 头部添加
        Event::listen('admin_top_begin', function ($data) {
            return  '<li class="layui-nav-item layui-hide-xs upgrade">
                <a href="javascript:;" class="layui-badge-rim ">'.config('badouadmin.version').'</a>
            </li>';
        });
        //插件市场 头部按钮
        Event::listen('module_top_btn', function ($data) {
            return  '<button type="button" class="layui-btn layui-bg-orange btn-frame-upgrade">
                <i class="fa fa-user"></i>
                '.__('Frame Upgrade').'
            </button>';
        });
    }
}

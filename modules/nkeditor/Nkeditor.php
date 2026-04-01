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

namespace modules\nkeditor;

use think\facade\Event;

class Nkeditor
{
    /**
     * 应用初始化时调用
     * @return array
     */
    public function AppInit()
    {
        Event::listen('editor', function () {});
        return [];
    }

    /**
     * 插件安装时调用
     * @return bool
     */
    public function install()
    {

        return true;
    }

    /**
     * 插件卸载时调用
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 插件启用时调用
     * @return bool
     */
    public function enable()
    {
        return true;
    }

    /**
     * 插件禁用时调用
     * @return bool
     */
    public function disable()
    {
        return true;
    }
}

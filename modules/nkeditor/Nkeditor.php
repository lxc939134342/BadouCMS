<?php

namespace modules\nkeditor;

use app\common\library\Menu;
use think\facade\Log;

class Nkeditor
{
    /**
     * 应用初始化时调用
     * @return array
     */
    public function AppInit()
    {
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

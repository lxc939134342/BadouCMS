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

namespace app\admin\controller\cms;

class Common extends Base
{
    protected array $noNeedLogin = ['loadlang'];

    /**
     * 加载语言包
     */
    public function loadlang()
    {
        $path = $this->request->param('path');
        if (!$path) {
            $this->error('path参数错误');
        }

        $lang = $this->request->param('lang', 'zh-cn');
        $lang = preg_match("/^([a-zA-Z\-_]{2,10})\$/i", $lang) ? $lang : 'zh-cn';

        $path = str_replace(['./backend/', './frontend/'], '', $path); // 移除前缀
        $path = str_replace('.ts', '', $path); // 移除 .ts 后缀
        $path = preg_replace('/^' . $lang . '\//', '', $path); // 移除开头的语言标识

        $name = preg_match("/^([a-zA-Z0-9_\.\/]+)\$/i", $path) ? $path : 'index';
        $name = preg_replace('/cms\/content\/mcode\/\d+$/', 'cms/content', $name);
        $pathfull = app_path() . 'lang' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $name) . '.php';
        $langarr = [];

        if (is_file($pathfull)) {
            $langarr = (array) include $pathfull;
        }
        $this->success('ok', $langarr);
    }
}

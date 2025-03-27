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

namespace bd;

use think\facade\Db;
use think\facade\Route;

class CmsRoute
{
    public function buildRoute(array $route): void
    {
        /* 变量规则 */
        Route::pattern([
            'category' => '\w+',
            'id'       => '[\w\-]+',
            'tag'      => '\w+',
        ]);

        /* 前台应用获取区域域名 */
        if (app('http')->getName() == 'index') {
            $cms_domain = cache('cms_domain');
            if (!$cms_domain) {
                $cms_domain = Db::name('cms_area')->column('domain', 'acode');
                cache('cms_domain', $cms_domain);
            }
        }

        $cms_domain   = array_diff($cms_domain, ['']);
        // $main_domain  = get_sys_config('main_domain');
        /* 设置主域名使用的语言 */
        // $cms_domain[get_default_lang()] = $main_domain ;

        $param = [];
        $lg = get_frontend_lang();
        $param = [
            'lg' => $lg
        ];
        /* 设置普通路由 */
        foreach ($route as $key => $value) {
            Route::rule($key, $value)->append($param);
        }

        /* 设置域名路由 */
        if ($cms_domain) {
            /* 匹配当前域名的语言 */
            $host = request()->host();
            if ($lg = array_search($host, $cms_domain)) {
                $param = [
                    'lg' => $lg
                ];
                set_forntend_lang($lg);
            }
            Route::domain(array_values($cms_domain), function () use ($route) {
                foreach ($route as $key => $value) {
                    Route::rule($key, $value);
                }
            })->append($param);
        }
    }
}

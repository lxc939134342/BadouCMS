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

use think\facade\Route;

$route_arr = [
    '/' => 'cms.index/index', // 首页访问路由
    'search$' => 'cms.search/index', // 搜索路由
    'user$' => 'cms.user/index', // 用户中心
    'user/:action$' => 'cms.user/:action', // 用户中心
    'account/:action$' => 'cms.account/:action',  // 账户中心
    'sitemap.xml$' => 'cms.sitemap/index', // sitemap路由
    'sitemap.txt$' => 'cms.sitemap/txt', // sitemap路由
    'tag/:tag$' => 'cms.tag/index',// tag路由
    'ajax/:action$' => 'ajax/:action',
    'do/:action$' => 'cms.index/:action',
    'upload$' => 'cms.base/upload',
    'captcha$' => 'cms.base/captcha',
    'message$' => 'cms.message/index',
    'message/submit_form$' => 'cms.message/submitForm',
    'comment/:action$' => 'cms.comment/:action',
    ':category$' => 'cms.lists/index',// 列表路由
    ':category/:id$' => 'cms.detail/index',// 详情路由
];


app('cms_route')->buildRoute($route_arr);


/* 无法匹配 */
Route::miss(function () {
    abort(404, '404 Not Found');
});

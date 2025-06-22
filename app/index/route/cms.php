<?php

use think\facade\Db;
use think\facade\Route;

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

$route_arr = [
    '/' => 'cms.index/index', // 首页访问路由
    'search$' => 'cms.search/index', // 搜索路由
    'user$' => 'user/index', // 用户中心
    'user/:action$' => 'user/:action', // 用户中心
    'account/:action$' => 'account/:action',  // 账户中心
    'sitemap.xml$' => 'cms.sitemap/index', // sitemap路由
    'sitemap.txt$' => 'cms.sitemap/txt', // sitemap路由
    'tag/:tag$' => 'cms.tag/index',// tag路由
    'ajax/:action$' => 'ajax/:action',
    'do/:action$' => 'cms.index/:action',
    'upload$' => 'cms.base/upload',
    'message$' => 'cms.message/index',
    'message/submit_form$' => 'cms.message/submitForm',
    'comment/:action$' => 'cms.comment/:action',
    ':category$' => 'cms.lists/index',// 列表路由
    ':category/:id$' => 'cms.detail/index',// 详情路由
];

/* 设置普通路由 */
foreach ($route_arr as $key => $value) {
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
    Route::domain(array_values($cms_domain), function () use ($route_arr) {
        foreach ($route_arr as $key => $value) {
            Route::rule($key, $value);
        }
    })->append($param);
}

<?php

use app\index\model\cms\Area;
use think\facade\Route;
use think\facade\Config;
use think\facade\Event;

/* 变量规则 */

Route::pattern([
    'category' => '\w+',
    'id'       => '[\w\-]+',
    'tag'      => '\w+',
]);

$cms_domain = [];
/* 前台应用获取区域域名 */
if (strtolower(app('http')->getName()) == 'index') {
    $cms_area = (new Area())->getList();
    Config::set(['area_list' => $cms_area], 'cms');
    $cms_domain = array_column($cms_area, 'domain', 'acode');
}

$cms_domain   = array_diff($cms_domain, ['']);
// $main_domain  = get_sys_config('main_domain');
/* 设置主域名使用的语言 */
// $cms_domain[get_default_lang()] = $main_domain ;

$lg = get_frontend_lang();

/* 路由初始化钩子：允许插件介入并修改当前的语言识别逻辑 */
$initRes = Event::trigger('cmsRouteInit', ['cms_area' => $cms_area]);
foreach ($initRes as $res) {
    if ($res && is_string($res)) {
        $lg = $res;
        break;
    }
}

/* 设置语言 */
if (request()->param('lg')) {
    $lg = request()->param('lg');
}

if (!$lg) {
    $default = array_shift($cms_area);
    $lg = $default['acode'];
}
set_forntend_lang($lg);
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
    'tag/:tag$' => 'cms.tag/index', // tag路由
    'ajax/:action$' => 'ajax/:action',
    'do/:action$' => 'cms.index/:action',
    'upload$' => 'cms.base/upload',
    'message$' => 'cms.message/index',
    'message/submit_form$' => 'cms.message/submitForm',
    'comment/:action$' => 'cms.comment/:action',
    ':category$' => 'cms.lists/index', // 列表路由
    ':category/:id$' => 'cms.detail/index', // 详情路由
];

/* 路由开始执行钩子：允许插件注入自定义路由或多语言组 */
Event::trigger('cmsRouteRun', ['route_arr' => $route_arr, 'param' => $param]);

/* cms事件执行前 */
Event::trigger('cms_route_before');
/* 设置普通路由 */
foreach ($route_arr as $key => $value) {
    Route::rule($key, $value)->append($param);
}
/* 设置域名路由 */
if ($cms_domain) {
    /* 匹配当前域名的语言 */
    $host = request()->host();
    if ($lg_domain = array_search($host, $cms_domain)) {
        $param_domain = [
            'lg' => $lg_domain
        ];
        set_forntend_lang($lg_domain);
    }
    Route::domain(array_values($cms_domain), function () use ($route_arr) {
        Event::trigger('cms_route_before');
        foreach ($route_arr as $key => $value) {
            Route::rule($key, $value);
        }
    })->append($param_domain ?? $param);
}

/* 路由执行结束钩子 */
Event::trigger('cmsRouteEnd');

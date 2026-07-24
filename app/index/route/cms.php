<?php

use app\index\model\cms\Area;
use think\facade\Route;
use think\facade\Config;
use think\facade\Event;

/* 变量规则 */

Route::pattern([
    'category' => '[\w\-]+',
    'id'       => '[\w\-]+',
    'tag'      => '[\w\-]+',
]);

$cms_domain = [];
$cms_area = [];
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
$defaultLanguage = get_default_lang();
$urlRuleType = (int)get_sys_config('url_rule_type');
$languageCodes = array_keys($cms_area);
$languagePattern = implode('|', array_map(
    static fn($language) => preg_quote(trim((string)$language), '#'),
    array_values(array_filter($languageCodes))
));

/* 路由初始化钩子：允许插件介入并修改当前的语言识别逻辑 */
$initRes = Event::trigger('cmsRouteInit', ['cms_area' => $cms_area]);
foreach ($initRes as $res) {
    if ($res && is_string($res)) {
        $lg = $res;
        break;
    }
}

/* 设置语言 */
$queryLanguage = trim((string)request()->param('lg', ''));
if ($queryLanguage !== '' && isset($cms_area[$queryLanguage])) {
    $lg = $queryLanguage;
}

// 目录模式以 URL 首段为准，确保 /en/... 在控制器初始化前就切换语言。
if ($urlRuleType === 1) {
    $path = trim((string)request()->pathinfo(), '/');
    $pathLanguage = $path === '' ? '' : strtok($path, '/');
    if ($pathLanguage !== false && isset($cms_area[$pathLanguage])) {
        $lg = $pathLanguage;
    }
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

/**
 * 注册目录模式下的语言前缀路由。
 *
 * 语言码通过 $languagePattern 限制，只允许已配置的前台语言；因此
 * /en/category/product.html 会被解析为 lg=en、category=category、id=product，
 * 但任意未知的第一段不会被当成语言。非目录模式不注册该分组，避免普通
 * 的多段路径被误判为语言路由。CMS、Shop 和 Inquiry 共用此入口，分别传入
 * 自己的路由表，避免按语言重复构建整套路由。
 *
 * @param array<string, string|array> $routes 路由规则及对应的控制器
 */
$registerLanguageRoutes = static function (array $routes) use ($languagePattern, $urlRuleType): void {
    if ($urlRuleType !== 1 || $languagePattern === '') {
        return;
    }

    Route::group(':lg', function () use ($routes) {
        foreach ($routes as $key => $value) {
            Route::rule($key, $value);
        }
    });
};

if ($urlRuleType === 1 && $languagePattern !== '') {
    Route::pattern(['lg' => $languagePattern]);
}

/* 路由开始执行钩子：允许插件注入自定义路由或多语言组 */
Event::trigger('cmsRouteRun', [
    'route_arr' => $route_arr,
    'param' => $param,
    'cms_area' => $cms_area,
    'languages' => $languageCodes,
    'url_rule_type' => $urlRuleType,
    'register_language_routes' => $registerLanguageRoutes,
]);

// 目录模式：使用一个受语言白名单约束的 /{lang}/ 路由组。
if ($urlRuleType === 1 && $languagePattern !== '') {
    Route::rule(':lg$', 'cms.index/index');
    Route::rule(':lg/$', 'cms.index/index');
    Route::rule(':lg/index$', 'cms.index/index');
    $registerLanguageRoutes($route_arr);
}

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
Event::trigger('cmsRouteEnd', [
    'cms_area' => $cms_area,
    'languages' => $languageCodes,
    'url_rule_type' => $urlRuleType,
    'register_language_routes' => $registerLanguageRoutes,
]);

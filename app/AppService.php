<?php

declare(strict_types=1);

namespace app;

use think\Service;
use think\facade\Event;

/**
 * 应用服务类
 */
class AppService extends Service
{
    public function register() {}

    public function boot()
    {
        // 服务启动
        error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
        ini_set('display_errors', '0');

        // 在视图和原有过滤器完成后处理 HTML，避免覆盖插件已经注册的 View::filter。
        $this->app->middleware->add(static function ($request, \Closure $next) {
            $response = $next($request);
            if (!Event::hasListener('ModuleViewFilter')
                || $response->getCode() >= 300
                || !str_starts_with(strtolower((string) $response->getHeader('Content-Type')), 'text/html')) {
                return $response;
            }

            $html = $response->getContent();
            if ($html === '') {
                return $response;
            }
            $context = (object) ['html' => $html];
            Event::trigger('ModuleViewFilter', $context);
            $response->content((string) $context->html);
            return $response;
        });
    }
}

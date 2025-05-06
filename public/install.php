<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// [ 安装文件 ]
// 建议安装完成后移除此文件
// 定义应用目录

// 加载框架引导文件

namespace think;

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '0');
use Throwable;

try {
    require __DIR__ . '/../vendor/autoload.php';
    // 执行HTTP应用并响应
    $app = new App();
    $http = $app->http;
    $route = $app->route;
    $route->rule('/', '\bd\service\install\Install::index');
    $response = $http->run();
    $response->send();

    $http->end($response);
} catch (Throwable $e) {
    header('Content-Type:text/html;charset=utf-8');
    $html = <<<EOF
依赖加载失败，重新下载完整程序包 或 执行 composer install 重新安装依赖<br> 查看文档<a href="http://www.badoucms.com/install.html" target="_blank">http://www.badoucms.com/install</a>
EOF;
    die($html);
}

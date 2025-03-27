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

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$app = new App();
$http = $app->http;
$route = $app->route;
$route->rule('/', '\bd\service\Install\Install::index');
$response = $http->run();
$response->send();

$http->end($response);

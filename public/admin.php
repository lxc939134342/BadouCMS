<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\App;

// [ 后台入口文件 ]

require __DIR__ . '/../vendor/autoload.php';

$rootPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR;
// 安装检测-s
//if (!is_file($rootPath . 'install.lock')) {
//    header("location:./install.php");
//    exit;
//}

// 安装检测-e
// 执行HTTP应用并响应
$http     = (new App())->http;
$response = $http->name('admin')->run();
$response->send();
$http->end($response);

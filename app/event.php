<?php
// 事件定义文件
// ModuleViewFilter 由 AppService 在 HTML 响应阶段触发，插件在 AppInit 中动态注册监听器。
return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
    ],

    'subscribe' => [
    ],
];

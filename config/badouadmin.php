<?php

return [
    //跨域域名
    'cors_request_domain' => 'localhost,127.0.0.1',
    //后台登录失败尝试次数
    'admin_failure_retry' => 10,
    //后台登录失败超过次数后锁定时间 默认1天（单位秒）
    'admin_failure_lock_time' => 86400,
    //后台登录保持时间 默认1天（单位秒）
    'admin_keep_time' => 86400,
    //登录验证码
    'admin_login_captcha' => true,
    //跳转页面模版
    'jump' => [
        'dispatch_success_tmpl' => app()->getBasePath() . '/common/view/tpl/dispatch_jump.tpl',
        'dispatch_error_tmpl'   => app()->getBasePath() . '/common/view/tpl/dispatch_jump.tpl',
    ],
    'version' => 'v1.0.0',
    'api_url' => 'http://sq.badoucms.test',
];

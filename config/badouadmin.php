<?php
return [
    //跨域域名
    'cors_request_domain'=>'localhost,127.0.0.1',
    //后台登录失败尝试次数
    'admin_failure_retry'=>10,
    //后台登录失败超过次数后锁定时间 默认1天（单位秒）
    'admin_failure_lock_time'=>86400,
    'jump'=>[
        'dispatch_success_tmpl' => app()->getBasePath() . '/common/view/tpl/dispatch_jump.tpl',
        'dispatch_error_tmpl'   => app()->getBasePath() . '/common/view/tpl/dispatch_jump.tpl',
    ]
];
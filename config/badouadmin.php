<?php

return [
    //跨域域名
    'cors_request_domain' => 'localhost,127.0.0.1,*',
    //后台登录失败尝试次数
    'admin_failure_retry' => 10,
    //后台登录失败超过次数后锁定时间 默认1天（单位秒）
    'admin_failure_lock_time' => 86400,
    //后台登录保持时间 默认1天（单位秒）
    'admin_keep_time' => 86400,
    //登录验证码
    'admin_login_captcha' => true,
    //后台登录提示
    'admin_main_warning' => true,
    //是否自动写入管理员日志
    'auto_write_admin_log' => true,
    //跳转页面模版
    'jump' => [
        'dispatch_success_tmpl' => app()->getBasePath() . '/common/view/tpl/dispatch_jump.tpl',
        'dispatch_error_tmpl'   => app()->getBasePath() . '/common/view/tpl/dispatch_jump.tpl',
    ],
    //会员中心
    'usercenter' => true,
    'user_login_captcha' => true,
    //用户注册验证码类型email/mobile/wechat/text/false
    'user_register_captcha' => 'text',
    //发送验证码前验证
    'user_api_captcha' => false,

    // Token 配置
    'token'                 => [
        // 默认驱动方式
        'default' => 'mysql',
        // 加密key
        'key'     => 'tcbDgmqLVzuAdNH39o0QnhOisvSCFZ7I',
        // 加密方式
        'algo'    => 'ripemd160',
        // 驱动
        'stores'  => [
            'mysql' => [
                'type'   => 'Mysql',
                // 留空表示使用默认的 Mysql 数据库，也可以填写其他数据库连接配置的`name`
                'name'   => '',
                // 存储token的表名
                'table'  => 'token',
                // 默认 token 有效时间
                'expire' => 2592000,
            ],
            'redis' => [
                'type'       => 'Redis',
                'host'       => '127.0.0.1',
                'port'       => 6379,
                'password'   => '',
                // Db索引，非 0 以避免数据被意外清理
                'select'     => 1,
                'timeout'    => 0,
                // 默认 token 有效时间
                'expire'     => 2592000,
                'persistent' => false,
                'prefix'     => 'tk:',
            ],
        ]
    ],
    //插件启用禁用时是否备份对应的全局文件
    'backup_global_files'   => true,
    // 升级时忽略升级的目录
    'upgrade_ignore_dirs' => ['template/cms/default/'],
    'version' => 'v2.2.0',
    'api_url' => 'https://sq.badoucms.com/',
    'module_init_key' => 'bW9kdWxlSW5pdA=='
];

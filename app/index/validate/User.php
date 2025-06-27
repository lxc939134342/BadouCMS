<?php

// +----------------------------------------------------------------------
// | BADOUCMS [ 八斗网站系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2024-2030 http://doc.ldcode.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: lande <939134342@qq.com>
// +----------------------------------------------------------------------

namespace app\index\validate;

use think\Validate;

class User extends Validate
{
    protected $failException = true;

    protected $rule = [
        'username'    => 'require|regex:^[a-zA-Z][a-zA-Z0-9_]{2,15}$|unique:user',
        'email'       => 'email|unique:user',
        'mobile'      => 'mobile|unique:user',
        'password'    => 'require|regex:^(?!.*[&<>"\'\n\r]).{6,32}$',
        'captcha'     => 'require',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'login'      => ['password'],
        'register'   => ['email', 'username', 'password', 'mobile', 'captcha'],
        'forgetpass' => ['password']
    ];

    public function __construct()
    {
        $this->field   = [
            'username'    => __('username'),
            'email'       => __('email'),
            'mobile'      => __('mobile'),
            'password'    => __('password'),
            'captcha'     => __('captcha'),
        ];
        $this->message = array_merge($this->message, [
            'username.regex' => __('Username must be 3 to 16 characters'),
            'password.regex' => __('Password must be 6 to 32 characters')
        ]);
        parent::__construct();
    }
}

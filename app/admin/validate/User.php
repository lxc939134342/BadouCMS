<?php

namespace app\admin\validate;

use think\Validate;

class User extends Validate
{
    protected $failException = true;

    /**
     * 验证规则
     */
    protected $rule = [
        'username' => 'require|regex:^[a-zA-Z][a-zA-Z0-9_]{2,15}$|unique:user',
        'nickname' => 'require|unique:user',
        'email'    => 'email|unique:user',
        'mobile'   => 'mobile|unique:user',
        'password' => 'regex:^(?!.*[&<>"\'\n\r]).{6,32}$',
    ];

    /**
     * 字段描述
     */
    protected $field = [
    ];
    /**
     * 提示消息
     */
    protected $message = [
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['username', 'nickname', 'password', 'email', 'mobile'],
        'edit' => ['username', 'nickname', 'password', 'email', 'mobile'],
    ];

    public function __construct()
    {
        $this->field = [
            'username' => __('Username'),
            'nickname' => __('Nickname'),
            'password' => __('Password'),
            'email'    => __('Email'),
            'mobile'   => __('Mobile')
        ];

        $this->message = array_merge($this->message, [
            'username.regex' => __('Username rule'),
            'password.regex' => __('Password must be 6 to 32 characters')
        ]);
        parent::__construct();
    }

}

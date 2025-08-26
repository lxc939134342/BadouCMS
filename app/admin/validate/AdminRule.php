<?php

namespace app\admin\validate;

use think\Validate;

class AdminRule extends Validate
{
    /**
     * 正则
     */
    protected $regex = ['format' => '[a-z0-9_\/]+'];

    /**
     * 验证规则
     */
    protected $rule = [
        'name|规则'  => 'require|unique:admin_rule|format',
        'title' => 'require',
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'name.format' => 'URL规则只能是小写字母、数字、下划线和/组成'
    ];

    protected $scene = [
        'add'
    ];
}

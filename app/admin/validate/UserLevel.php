<?php

namespace app\admin\validate;

use think\Validate;

class UserLevel extends Validate
{
    protected $failException = true;

    /**
     * 验证规则
     */
    protected $rule = [
        'gcode|等级编号' => 'require|unique:user_level'
    ];

    /**
     * 提示消息
     */
    protected $message = [
    ];

}

<?php

namespace app\admin\validate\cms;

use think\Validate;

class Extfield extends Validate
{
    protected $failException = true;

    /**
     * 验证规则
     */
    protected $rule = [
        'mcode|模型' => 'require',
        'description|字段描述' => 'require',
        'name|字段名称' => 'require',
        'type|字段类型' => 'require'
    ];

    /**
     * 提示消息
     */
    protected $message = [];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];
}

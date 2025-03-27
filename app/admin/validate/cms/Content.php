<?php

namespace app\admin\validate\cms;

use think\Validate;

class Content extends Validate
{
    protected $failException = true;

    /**
     * 验证规则
     */
    protected $rule = [
        'scode|栏目' => 'require',
        'title|标题' => 'require',
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

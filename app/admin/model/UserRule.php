<?php

namespace app\admin\model;

use think\Model;

class UserRule extends Model
{
    protected $autoWriteTimestamp = true;

    // 追加属性
    protected $append = [
        'status_text'
    ];

}
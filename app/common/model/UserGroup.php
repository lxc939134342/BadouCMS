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

namespace app\common\model;

use think\Model;

class UserGroup extends Model
{
    // 表名
    protected $name = 'user_group';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 追加属性
    protected $append = [
    ];

}

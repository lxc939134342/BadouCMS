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

namespace app\admin\model;

use think\Model;

class AdminGroup extends Model
{
    protected $autoWriteTimestamp  = true;
    protected $type = [
        'create_time' => 'int',
        'update_time' => 'int',
    ];


    public function getNameAttr($value, $data)
    {
        return __($value);
    }
}

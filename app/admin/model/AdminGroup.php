<?php

namespace app\admin\model;

use think\Model;

class AdminGroup extends Model
{
    protected $autoWriteTimestamp  = true;

    public function getNameAttr($value, $data)
    {
        return __($value);
    }
}

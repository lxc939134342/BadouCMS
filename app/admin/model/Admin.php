<?php

namespace app\admin\model;

use think\Model;

class Admin extends Model
{
    /**
     * @var string 自动写入时间戳
     */
    protected $autoWriteTimestamp = true;

    protected $name  = 'admin';

    protected $hidden = [
        'password'
    ];
}
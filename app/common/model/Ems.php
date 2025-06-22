<?php

namespace app\common\model;

use think\Model;

/**
 * 邮箱验证码
 */
class Ems extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 定义时间戳字段名
    protected $updateTime = false;
}

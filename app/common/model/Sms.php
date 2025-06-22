<?php

namespace app\common\model;

use think\Model;

/**
 * 短信验证码
 */
class Sms extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
}

<?php

namespace app\admin\model;

use think\Model;

class AdminGroup extends Model
{
    //设置为 true 时，表示启用自动写入时间戳功能。ThinkPHP 会在创建或更新数据时，自动将当前时间写入数据库表中对应的时间戳字段（如 create_time 和 update_time）
    protected $autoWriteTimestamp  = true;

}
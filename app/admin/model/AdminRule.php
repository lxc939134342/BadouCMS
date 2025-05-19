<?php

namespace app\admin\model;

use think\facade\Cache;
use think\Model;

class AdminRule extends Model
{
    const RULE_URL  = 1;
    const RULE_MAIN = 2; //主菜单
    protected $autoWriteTimestamp = true;

    public static function onAfterWrite($row)
    {
        Cache::delete('__menu__');
    }
}
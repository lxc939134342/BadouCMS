<?php

namespace app\admin\model;

use think\facade\Cache;
use think\Model;

class AdminRule extends Model
{
    public const RULE_URL  = 1;
    public const RULE_MAIN = 2; //主菜单
    protected $autoWriteTimestamp = true;

    public static function onAfterWrite($row)
    {
        Cache::delete('__menu__');
    }

    public function getTitleAttr($value, $data)
    {
        return __($value);
    }
}

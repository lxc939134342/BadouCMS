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

namespace app\admin\model\cms;

use think\Model;
use think\facade\Cache;

/**
 * Company
 */
class Company extends Model
{
    // 表名
    protected $name = 'cms_company';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    public static function onAfterWrite($model)
    {
        Cache::tag('cms_cache')->clear();
    }

    public static function onAfterDelete($model)
    {
        Cache::tag('cms_cache')->clear();
    }
}

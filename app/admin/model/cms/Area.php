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

/**
 * Area
 */
class Area extends Model
{
    // 表名
    protected $name = 'cms_area';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    protected $append = [
        'is_default_text'
    ];

    public function getIsDefaultTextAttr($value, $data)
    {
        return $data['is_default'] == 1 ? __('是') : __('否');
    }

    public static function onAfterWrite($model): void
    {
        $data = $model->getData();
        /* 设置默认 */
        if (isset($data['is_default']) && $data['is_default'] == 1) {
            self::where('id', '<>', $model->id)->update(['is_default' => 0]);
        }
    }

    public static function onBeforeDelete($model): void
    {
        $data = $model->getData();
        if ($data['acode'] == 'cn') {
            throw new \Exception(__('The Chinese area is not allowed to be deleted'));
        }
        if ($data['is_default'] == 1) {
            throw new \Exception(__('The default region is not allowed for deletion'));
        }
    }
}

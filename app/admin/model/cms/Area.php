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
        Cache::delete('cms_default_lang');
        /* 设置默认 */
        if (isset($data['is_default']) && $data['is_default'] == 1) {
            self::where('id', '<>', $model->id)->update(['is_default' => 0]);
        }
    }

    public static function onBeforeDelete($model): void
    {
        $data = $model->getData();

        // 检测是否为默认区域
        if ($data['is_default'] == 1) {
            throw new \Exception(__('The default region is not allowed for deletion'));
        }

        // 检测区域是否处于开启状态
        if ($data['status'] == 1) {
            throw new \Exception(__('Cannot delete area that is currently enabled'));
        }

        // 检测是否为中文区域
        if ($data['acode'] == 'cn') {
            // 检测是否有其他区域存在
            $otherAreasCount = self::where('acode', '<>', 'cn')->count();
            if ($otherAreasCount == 0) {
                throw new \Exception(__('Chinese area cannot be deleted when there are no other areas'));
            }
        }

        // 检测该区域下是否有栏目
        $contentSortCount = \app\admin\model\cms\ContentSort::where('acode', $data['acode'])->count();
        if ($contentSortCount > 0) {
            throw new \Exception(__('Cannot delete area with existing categories'));
        }

        // 检测该区域下是否有内容
        $contentCount = \app\admin\model\cms\Content::where('acode', $data['acode'])->count();
        if ($contentCount > 0) {
            throw new \Exception(__('Cannot delete area with existing content'));
        }
    }

    public function areaList()
    {
        $langs = $this->where('status', 1)->order('is_default DESC,id ASC')->column('id,acode,name');
        return $langs;
    }

    public function defaultArea()
    {
        $area = $this->where('is_default', 1)->field('id,acode,name')->find();
        return $area;
    }

    public static function onAfterDelete($model): void
    {
        $data = $model->getData();

        // 清空该区域的站点配置
        \app\admin\model\cms\Site::where('acode', $data['acode'])->delete();

        // 清空该区域的公司信息
        \app\admin\model\cms\Company::where('acode', $data['acode'])->delete();
    }
}

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
use think\facade\Db;
use think\facade\Cache;

/**
 * Label
 */
class Label extends Model
{
    // 表名
    protected $name = 'cms_label';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    public function typeList(): array
    {
        $options = [
            '1' => ['text' => '单行文本', 'inputType' => 'string', 'type' => 'string', 'limit' => 100, 'default' => ''],
            '7' => ['text' => '多行文本', 'inputType' => 'textarea', 'type' => 'string', 'limit' => 1000, 'default' => ''],
            '2' => ['text' => '日期选择', 'inputType' => 'datetime', 'type' => 'datetime', 'limit' => 0, 'default' => null],
            '3' => ['text' => '单图', 'inputType' => 'image', 'type' => 'string', 'limit' => 255, 'default' => null],
            '4' => ['text' => '附件上传', 'inputType' => 'files', 'type' => 'string', 'limit' => 255, 'default' => ''],
            '5' => ['text' => '编辑器', 'inputType' => 'editor', 'type' => 'text', 'limit' => 0, 'default' => ''],
            '6' => ['text' => '开关', 'inputType' => 'switch', 'type' => 'string', 'limit' => 255, 'default' => ''],
            '8' => ['text' => '多图上传', 'inputType' => 'images', 'type' => 'string', 'limit' => 1000, 'default' => ''],
            '9'  => ['text' => '下拉选择', 'inputType' => 'select', 'type' => 'string', 'limit' => 255, 'default' => ''],
            '11'  => ['text' => '多图标题(只增加字段)', 'inputType' => 'imagestitle', 'type' => 'string', 'limit' => 1000, 'default' => ''],
            '12'  => ['text' => '二列数组', 'inputType' => 'array', 'type' => 'text', 'limit' => 0, 'default' => null],
            '15'  => ['text' => '三列数组', 'inputType' => 'array3', 'type' => 'text', 'limit' => 0, 'default' => null]

        ];

        return $options;
    }

    /* 类型文字 */
    public function typeListTextMap(): array
    {
        $map  = [];
        $list = $this->typeList();
        foreach ($list as $key => $value) {
            $map[$key] = $value['text'];
        }
        return $map;
    }

    /* 类型组件 */
    public function typeListComponentMap(): array
    {
        $map  = [];
        $list = $this->typeList();
        foreach ($list as $key => $value) {
            $map[$key] = $value['inputType'];
        }
        return $map;
    }

    /* 插入后 */
    public static function onAfterInsert($model)
    {
        $data = $model->getData();
        // 如果是多图上传(3)，自动创建多图标题(11)
        if ($data['type'] == '3') {
            $name = $data['name'] . 'title';
            $titleData = [
                'acode'       => $data['acode'] ?? get_backend_lang(),
                'name'        => $name,
                'description' => $data['description'] . '标题',
                'type'        => '11',
                'value'       => '',
                'sorting'     => $data['sorting'] + 1,
                'create_user' => $data['create_user'] ?? '',
                'update_user' => $data['update_user'] ?? '',
            ];
            (new self())->save($titleData);
        }
        Cache::tag('cms_cache')->clear();
    }

    /* 更新后 */
    public static function onAfterUpdate($model)
    {
        $data = $model->getData();
        $originData = $model->getOrigin();

        if ($originData['type'] == '3') {
            $oldTitleName = $originData['name'] . 'title';
            $newTitleName = $data['name'] . 'title';
            $acode = $data['acode'] ?? get_backend_lang();

            $titleField = self::where('name', $oldTitleName)->where('acode', $acode)->find();
            if ($titleField) {
                $updateData = [];
                if ($oldTitleName != $newTitleName) {
                    $updateData['name'] = $newTitleName;
                }
                if ($originData['sorting'] != $data['sorting']) {
                    $updateData['sorting'] = $data['sorting'] + 1;
                }
                if ($updateData) {
                    $titleField->save($updateData);
                }
            }
        }
        Cache::tag('cms_cache')->clear();
    }

    /* 删除后 */
    public static function onAfterDelete($model)
    {
        $data = $model->getData();
        if ($data['type'] == '3') {
            $titleName = $data['name'] . 'title';
            $acode = $data['acode'] ?? get_backend_lang();
            self::where('name', $titleName)->where('acode', $acode)->delete();
        }
        Cache::tag('cms_cache')->clear();
    }

    public function getValueAttr($value, $data)
    {
        if (!$value) {
            return '';
        }
        return htmlspecialchars_decode_improve($value);
    }
}

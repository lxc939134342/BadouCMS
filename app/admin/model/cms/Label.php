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
            '1' => ['text' => '单行文本', 'designType' => 'string', 'type' => 'string', 'limit' => 100, 'default' => ''],
            '7' => ['text' => '多行文本', 'designType' => 'textarea', 'type' => 'string', 'limit' => 1000, 'default' => ''],
            '2' => ['text' => '日期选择', 'designType' => 'datetime', 'type' => 'datetime', 'limit' => 0, 'default' => null],
            '3' => ['text' => '多图上传', 'designType' => 'images', 'type' => 'string', 'limit' => 1000, 'default' => ''],
            '4' => ['text' => '附件上传', 'designType' => 'files', 'type' => 'string', 'limit' => 255, 'default' => ''],
            '5' => ['text' => '编辑器', 'designType' => 'editor', 'type' => 'text', 'limit' => 0, 'default' => ''],
            '6' => ['text' => '开关', 'designType' => 'radio', 'type' => 'string', 'limit' => 255, 'default' => '']
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
            $map[$key] = $value['designType'];
        }
        return $map;
    }

    public function getValueAttr($value, $data)
    {
        if (!$value) {
            return '';
        }
        return htmlspecialchars_decode_improve(htmlspecialchars_decode_improve(html_entity_decode($value)));
    }
}

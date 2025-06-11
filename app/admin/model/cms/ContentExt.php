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

class ContentExt extends Model
{
    // 表名
    protected $name = 'cms_content_ext';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    /**
     * 获取内容扩展数据
     * @param mixed $data
     * @return array
     */
    public function getExtData($data, $mcode = 0): array
    {
        $fieldsTypeMap = [];
        if ($mcode) {
            $fields = (new Extfield())->getModelFields($mcode);
            foreach ($fields as $field) {
                $fieldsTypeMap[$field['name']] = $field['component'];
            }
        }

        $extdata = [];
        foreach ($data as $key => $value) {
            if (preg_match('/^ext_[\w\-]+$/', $key)) {
                if (isset($fieldsTypeMap[$key]) && $fieldsTypeMap[$key] == 'editor') {
                    $value = replaceEditorDomain(clean_xss($value), request()->domain());
                    $extdata[$key] = $value;
                } elseif (is_array($value)) {
                    $extdata[$key] = implode(',', $value);
                } elseif (is_string($value)) {
                    /* 兼容 windows与linux */
                    $extdata[$key] = str_replace(["\r\n", "\n"], '<br>', $value);
                } else {
                    $extdata[$key] = $value;
                }
            }
        }
        return $extdata;
    }

    /**
     * 数据转换
     * @param string $mcode
     * @param mixed $data
     * @return array
     */
    public function formatValue($mcode, $data)
    {
        $fields = (new Extfield())->getModelFields($mcode);
        $fieldsTypeMap = [];
        foreach ($fields as $field) {
            $fieldsTypeMap[$field['name']] = $field['component'];
        }

        $extdata = [];
        foreach ($data as $key => $value) {
            if (!isset($fieldsTypeMap[$key])) {
                continue;
            }
            if (!$value) {
                $extdata[$key] = $value;
                continue;
            }
            switch ($fieldsTypeMap[$key]) {
                case 'checkbox':
                case 'radio':
                    $value = explode(',', $value);
                    $extdata[$key] =  $value;
                    break;
                case 'textarea':
                    $extdata[$key] =  str_replace('<br>', "\r\n", $value);
                    break;
                case 'editor':
                    $extdata[$key] = addEditorDomain(decode_string($value), request()->domain());
                    break;
                default:
                    $extdata[$key] = $value;
            }
        }
        return $extdata;
    }
}

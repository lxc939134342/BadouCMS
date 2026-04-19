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

namespace app\index\model\cms;

use think\Model;

class Extfield extends Model
{
    protected $name = 'cms_extfield';

    /**
     * 获取筛选条件数据
     * @param mixed $field
     * @return void
     */
    public static function getSelect($field, $multiple = 0): array
    {
        $data = self::where('name', $field)->value('value');
        $result = [];

        if ($data) {
            /*解析url参数*/
            $info = parse_url(request()->url());
            $query = [];
            if (isset($info['query'])) {
                parse_str($info['query'], $query);
            }
            // 去掉page参数
            if (isset($query['page'])) {
                unset($query['page']);
            }
            $item = [];
            $data = parse_array($data);
            $key = 1;

            // 智能判断当前字段配置是否为多选类型（根据前端模板控制: multiple=1）默认单选
            $isMultiSelect = (int)$multiple === 1;

            // 获取当前已选中的值列表（支持逗号分隔的选项）
            $currentRaw = request()->get($field);
            $selectedValues = ($currentRaw !== null && $currentRaw !== '')
                ? array_filter(array_map('trim', explode(',', $currentRaw)))
                : [];

            foreach ($data as $k => $value) {
                $item['n'] = $key - 1;
                $item['i'] = $key;
                $item['value'] = $value;
                $item['text'] = __($value); // 恢复国际化翻译

                // 判断当前选项是否已选中
                $isSelected = in_array((string)$value, array_map('strval', $selectedValues));
                $item['current'] = $isSelected ? 1 : 0;

                // 重点：智能切换组装逻辑（单选 OR 多选）
                if ($isMultiSelect) {
                    $newSelected = $selectedValues;
                    // 多选逻辑：已选则剔除，未选则追加
                    if ($isSelected) {
                        $newSelected = array_values(array_filter($newSelected, function($v) use ($value) {
                            return (string)$v !== (string)$value;
                        }));
                    } else {
                        $newSelected[] = (string)$value;
                    }
                } else {
                    // 单选逻辑：已选则清空取消，未选则完全覆盖为单值
                    if ($isSelected) {
                        $newSelected = [];
                    } else {
                        $newSelected = [(string)$value];
                    }
                }

                // 生成新链接
                $newQuery = $query;
                if (empty($newSelected)) {
                    unset($newQuery[$field]);
                } else {
                    $newQuery[$field] = implode(',', $newSelected);
                }
                $item['link'] = empty($newQuery) ? $info['path'] : '?' . http_build_query($newQuery);

                $key++;
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * 获取筛选条件标签
     * @param mixed $params
     * @return string
     */
    public static function getSelectAllLabel($params): string
    {
        /*解析url参数*/
        $info = parse_url(request()->url());
        $query = [];
        if (isset($info['query'])) {
            parse_str($info['query'], $query);
            unset($query[$params['field']]);
        }
        $link = empty($query) ? $info['path'] : '?' . http_build_query($query);
        $text = $params['text'] ?: __('All');
        $class = $params['class'] ?: 'btn btn-default btn-sm';
        $active = $params['active'] ?: 'active';
        if (request()->get($params['field']) === null) {
            $out_html = '<a href="' . $link . '" class="' . $active . '">' . $text . '</a>';
        } else {
            $out_html = '<a href="' . $link . '" class="' . $class . '">' . $text . '</a>';
        }

        return $out_html;
    }
}

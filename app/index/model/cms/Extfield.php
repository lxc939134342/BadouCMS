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
    public static function getSelect($field): array
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
            $item = [];
            $data = explode(',', $data);
            $key = 1;
            foreach ($data as $k => $value) {
                $query[$field] = $value;
                $item['n'] = $key - 1;
                $item['i'] = $key;
                $item['value'] = $value;
                $item['current'] = (request()->get($field) !== null && request()->get($field) == $value) ? 1 : 0;
                $item['link'] = empty($query) ? $info['path'] : '?'.http_build_query($query);
                $key++;
                $result[] = $item;
            }

        }
        return $result;
    }

    /**
     * 获取筛选条件标签
     * @param mixed $paams
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
        $link = empty($query) ? $info['path'] : '?'.http_build_query($query);
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

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

class Slide extends \think\Model
{
    protected $name = 'cms_slide';

    protected $append = [
        'src'
    ];

    protected function getPicAttr($value, $data)
    {
        return $value ? cdnurl($value) : '';
    }

    protected function getSrcAttr($value, $data)
    {
        return $data['pic'];
    }


    public static function slideList($gid, $num = 5)
    {
        $selfModel = new self();
        $where = [
            'gid' => $gid,
            'acode' => get_frontend_lang()
        ];
        $res = $selfModel->where($where)
            ->order('sorting ASC ,id DESC')
            ->limit($num)->select();
        if ($res->isEmpty()) {
            return [];
        }
        $res = $res->toArray();
        foreach ($res as $key => $value) {
            $res[$key]['n'] = $key;
            $res[$key]['i'] = $key + 1;
        }
        return $res;
    }
}

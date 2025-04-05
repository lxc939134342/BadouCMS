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
    protected function getSrcAttr($value, $data)
    {
        if ($data['pic']) {
            if (! preg_match('/^http/', $data['pic'])) {
                return full_url($data['pic']);
            } else {
                return $data['pic'];
            }
        }
        return $data['pic'];
    }


    public static function slideList($gid, $num = 5)
    {
        $selfModel = new self();
        $res = $selfModel->where('gid', $gid)->order('sorting asc')->limit($num)->select();
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

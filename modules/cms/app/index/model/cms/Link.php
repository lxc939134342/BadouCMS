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

class Link extends \think\Model
{
    protected $name = 'cms_link';

    public static function linkList($gid, $num = 10)
    {
        $selfModel = new self();
        $res = $selfModel->where('gid', $gid)->limit($num)->select();
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

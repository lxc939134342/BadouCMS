<?php

namespace app\admin\controller;

use app\common\controller\Backend;

class Module extends Backend
{
    public function index()
    {
        if ($this->isAjax()) {
            $list = [
                [
                    "id" => 1,
                    "image" => "https://gw.alipayobjects.com/zos/rmsportal/gLaIAoVWTtLbBWZNYEMg.png",
                    "title" => "Alipay",
                    "remark" => "那是一种内在的东西， 他们到达不了，也无法触及的",
                    "time" => "几秒前"
                ],
                [
                    "id" => 1,
                    "image" => "https://gw.alipayobjects.com/zos/rmsportal/gLaIAoVWTtLbBWZNYEMg.png",
                    "title" => "Alipay",
                    "remark" => "那是一种内在的东西， 他们到达不了，也无法触及的",
                    "time" => "几秒前"
                ],
                [
                    "id" => 1,
                    "image" => "https://gw.alipayobjects.com/zos/rmsportal/gLaIAoVWTtLbBWZNYEMg.png",
                    "title" => "Alipay",
                    "remark" => "那是一种内在的东西， 他们到达不了，也无法触及的",
                    "time" => "几秒前"
                ],
                [
                    "id" => 1,
                    "image" => "https://gw.alipayobjects.com/zos/rmsportal/gLaIAoVWTtLbBWZNYEMg.png",
                    "title" => "Alipay",
                    "remark" => "那是一种内在的东西， 他们到达不了，也无法触及的",
                    "time" => "几秒前"
                ]
            ];
            $this->success('ok', null, $list);
        }
        return $this->view->fetch();
    }
}

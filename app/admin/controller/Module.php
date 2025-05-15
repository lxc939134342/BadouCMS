<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use badou\Service;

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

        $modules = Service::getModuleList();
        foreach ($modules as $k => &$v) {
            $v['url'] = str_replace($this->request->server('SCRIPT_NAME'), '', $v['url']);
        }
        $this->assignconfig(['modules' => $modules, 'api_url' => config('badouadmin.api_url'), 'faversion' => config('badouadmin.version'), 'domain' => request()->host(true)]);
        return $this->view->fetch();
    }
}

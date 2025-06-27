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

namespace app\admin\controller\general;

use badou\Random;
use think\facade\Session;
use think\facade\Validate;
use app\common\controller\Backend;

class Profile extends Backend
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Admin();
    }

    public function index()
    {
        return $this->view->fetch();
    }

    public function edit()
    {
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a");
            $params = array_filter(array_intersect_key(
                $params,
                array_flip(array('email', 'nickname', 'password', 'avatar'))
            ));
            unset($v);
            if (!Validate::is($params['email'], "email")) {
                $this->error(__("Please input correct email"));
            }
            if (isset($params['password'])) {
                if (!Validate::is($params['password'], "/^[\S]{6,30}$/")) {
                    $this->error(__("Please input correct password"));
                }
                $params['salt'] = Random::build();
                $params['password'] = $this->auth->getEncryptPassword($params['password']);
            }
            $exist =  $this->model::where('email', $params['email'])->where('id', '<>', $this->auth->id)->find();
            if ($exist) {
                $this->error(__("Email already exists"));
            }
            if ($params) {
                $admin = $this->model::find($this->auth->id);
                $admin->save($params);
                //因为个人资料面板读取的Session显示，修改自己资料后同时更新Session
                Session::set("admin", $admin->toArray());
                Session::set("admin.safecode", $this->auth->getEncryptSafecode($admin));
                $this->success();
            }
            $this->error();
        }
        return;
    }
}

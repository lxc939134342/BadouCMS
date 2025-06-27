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

namespace app\admin\controller;

use app\admin\model\AdminLog;
use app\common\controller\Backend;
use think\facade\Config;
use think\facade\Event;

class Index extends Backend
{
    protected $noNeedLogin = ['login'];
    protected $noNeedRight = ['index', 'logout','adminConfig'];

    public function index()
    {
        if ($this->request->isAjax()) {
            $menus = $this->auth->getMenus($this->auth->id);
            return json($menus);
        }
        return $this->view->fetch();
    }

    public function adminConfig()
    {
        $adminConfig = [
            "logo" => [
                "title" => get_sys_config('site_name'),
                "image" => "/assets/images/logo.png"
            ],
            "menu" => [
                "data" => (string)url('/index/index'),
                "method" => "GET",
                "accordion" => true,
                "collapse" => false,
                "control" => false,
                "controlWidth" => 500,
                "select" => "10",
                "async" => true
            ],
            "tab" => [
                "enable" => true,
                "keepState" => true,
                "session" => true,
                "preload" => false,
                "max" => "30",
                "index" => [
                    "id" => "1",
                    "href" => (string)url('/dashboard/index'),
                    "title" => __('Dashboard'),
                    "hash" => 'dashboard'
                ]
            ],
            "theme" => [
                "defaultColor" => "2",
                "defaultMenu" => "dark-theme",
                "defaultHeader" => "light-theme",
                "allowCustom" => true,
                "banner" => false,
                "dark" => true
            ],
            "colors" => [
                [
                    "id" => "1",
                    "color" => "#2d8cf0",
                    "second" => "#ecf5ff"
                ],
                [
                    "id" => "2",
                    "color" => "#67c23a",
                    "second" => "#f0f9eb"
                ],
                [
                    "id" => "3",
                    "color" => "#ff6c02",
                    "second" => "#fdf6ec"
                ],
                [
                    "id" => "4",
                    "color" => "#f56c6c",
                    "second" => "#fef0f0"
                ],
                [
                    "id" => "5",
                    "color" => "#4e73df",
                    "second" => "#ecf5ff"
                ],
            ],
            "other" => [
                "keepLoad" => "500",
                "autoHead" => false,
                "footer" => false
            ],
            "header" => [
                "message" => false
            ]
        ];
        return response($adminConfig, 200, [], 'json');
    }

    public function login()
    {
        $url = $this->request->get('url', '', 'url_clean');
        $url = $url ?: rtrim(url("/", [], false), '/');
        if ($this->auth->isLogin()) {
            $this->success(__("You've logged in, do not login again"), $url);
        }
        if ($this->isAjax()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password', '', null);
            $captcha = $this->request->post('captcha');
            $keeplogin = $this->request->post('keeplogin', false);
            //验证token
            if (!$this->request->checkToken('__token__')) {
                $this->error(__('Token verification error'), $url, ['token' => $this->request->buildToken()]);
            }
            $rule = [
                'username|用户名'  => 'require',
                'password|密码'  => 'require',
                'captcha|验证码'   => 'require|captcha',
            ];
            $data = [
                'username' => $username,
                'password' => $password,
                'captcha'  => $captcha,
            ];
            $validate = validate($rule, [], false, false);
            if (!$validate->check($data)) {
                $this->error($validate->getError(), $url, ['token' => $this->request->buildToken()]);
            }

            AdminLog::instance()->setTitle(__('Login'));
            $admin_keep_time = Config::get('badouadmin.admin_keep_time');
            $result = $this->auth->login($username, $password, $keeplogin ? $admin_keep_time : 0);

            if ($result === true) {
                Event::trigger('admin_login_arter', $this->request);
                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, ['token' => $this->request->buildToken()]);
            }
        }
        return $this->view->fetch();
    }

    public function logout()
    {
        if ($this->isAjax()) {
            $this->auth->logout();
            Event::trigger('admin_logout_after', $this->request);
            $this->success(__('Logout successful'), 'index/login');
        }
    }
}

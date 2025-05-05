<?php

namespace app\admin\controller;

use app\admin\model\AdminLog;
use app\common\controller\Backend;
use think\facade\Config;
use think\facade\Event;
use think\Hook;
use think\Validate;

class Index extends Backend
{
    protected $noNeedLogin = ['login'];
    protected $noNeedRight = ['index', 'logout','adminConfig'];
    public function index()
    {
        return $this->view->fetch();
    }

    public function adminConfig()
    {
        $adminConfig = [
            "logo" => [
                "title" => "BADOUADMIN",
                "image" => "/static/admin/images/logo.png"
            ],
            "menu" => [
                "data" => "/admin/index/index",
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
                    "href" => "/admin/dashboard/index?view=1",
                    "title" => "首页"
                ]
            ],
            "theme" => [
                "defaultColor" => "2",
                "defaultMenu" => "dark-theme",
                "defaultHeader" => "light-theme",
                "allowCustom" => true,
                "banner" => false
            ],
            "colors" => [
                [
                    "id" => "1",
                    "color" => "#2d8cf0",
                    "second" => "#ecf5ff"
                ],
                [
                    "id" => "2",
                    "color" => "#36b368",
                    "second" => "#f0f9eb"
                ],
                [
                    "id" => "3",
                    "color" => "#f6ad55",
                    "second" => "#fdf6ec"
                ],
                [
                    "id" => "4",
                    "color" => "#f56c6c",
                    "second" => "#fef0f0"
                ],
                [
                    "id" => "5",
                    "color" => "#3963bc",
                    "second" => "#ecf5ff"
                ]
            ],
            "other" => [
                "keepLoad" => "1200",
                "autoHead" => false,
                "footer" => false
            ],
            "header" => [
                "message" => "admin/data/message.json"
            ]
        ];
        return response($adminConfig, 200, [], 'json');
    }

    public function login(){
        $url = $this->request->get('url', '', 'url_clean');
        $url = $url ?: 'index/index';
        if ($this->auth->isLogin()) {
            $this->success(__("You've logged in, do not login again"), $url);
        }
        if($this->request->isAjax()){
            $username = $this->request->post('username');
            $password = $this->request->post('password', '', null);
            $keeplogin = $this->request->post('keeplogin',false);
            //验证token
            if (!$this->request->checkToken('__token__')) {
                $this->error(__('Token verification error'), $url, ['token' => $this->request->buildToken()]);
            }
            $rule = [
                'username'  => 'require',
                'password'  => 'require',
            ];
            $msg = [
                'username.require' => '请输入用户名',
                'password.require' => '请输入密码',
            ];
            $data=[
                'username'=>$username,
                'password'=>$password,
            ];
            $validate = validate($rule,$msg);
            if(!$validate->check($data)){
                $this->error($validate->getError(),$url,['token'=>$this->request->buildToken()]);
            }
            AdminLog::instance()->setTitle(__('Login'));
            $admin_keep_time= Config::get('badouadmin.admin_keep_time');
            $result = $this->auth->login($username, $password, $keeplogin ? $admin_keep_time : 0);
            if ($result === true) {
                Event::trigger('admin_login_arter',$this->request);
                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, ['token' => $this->request->buildToken()]);
            }
        }
        return $this->view->fetch();
    }
}
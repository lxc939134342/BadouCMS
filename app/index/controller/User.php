<?php
/*
** +----------------------------------------------------------------------
** | Wusn
** +----------------------------------------------------------------------
** | Copyright (c) Wusn All rights reserved.
** +----------------------------------------------------------------------
** | Author: Wusn <958342972@qq.com>
** +----------------------------------------------------------------------
** | DateTime: 2025/6/14 16:24
** +----------------------------------------------------------------------
*/

namespace app\index\controller;

use think\Config;
use think\Cookie;
use think\Hook;

class User extends Base
{
    protected $layout = 'default';
    protected $noNeedLogin = ['login', 'register', 'third'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $auth = $this->auth;

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'), '/');
        }

//        //监听注册登录退出的事件
//        Hook::add('user_login_successed', function ($user) use ($auth) {
//            $expire = input('post.keeplogin') ? 30 * 86400 : 0;
//            Cookie::set('uid', $user->id, $expire);
//            Cookie::set('token', $auth->getToken(), $expire);
//        });
//        Hook::add('user_register_successed', function ($user) use ($auth) {
//            Cookie::set('uid', $user->id);
//            Cookie::set('token', $auth->getToken());
//        });
//        Hook::add('user_delete_successed', function ($user) use ($auth) {
//            Cookie::delete('uid');
//            Cookie::delete('token');
//        });
//        Hook::add('user_logout_successed', function ($user) use ($auth) {
//            Cookie::delete('uid');
//            Cookie::delete('token');
//        });
    }

    public function index()
    {
        $this->view->assign('title', __('User center'));
        return $this->view->fetch();
    }

    /**
     ** 退出登录
     ** | Author: Wusn <958342972@qq.com>
     ** | @return string
     **
     ** | DateTime: 2025/6/14 16:28
     */
    public function logout()
    {
        if ($this->request->isPost()) {
            $this->token();
            //退出本站
            $this->auth->logout();
            $this->success(__('Logout successful'), url('user/index'));
        }
        $html = "<form id='logout_submit' name='logout_submit' action='' method='post'>" . token() . "<input type='submit' value='ok' style='display:none;'></form>";
        $html .= "<script>document.forms['logout_submit'].submit();</script>";

        return $html;
    }

    /**
     ** 个人信息
     ** | Author: Wusn <958342972@qq.com>
     ** | @return mixed
     **
     ** | DateTime: 2025/6/14 16:28
     */
    public function profile()
    {
        $this->view->assign('title', __('Profile'));
        return $this->view->fetch();
    }
}
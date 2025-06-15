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

use app\common\controller\Frontend;
use think\Config;
use think\Cookie;
use think\Hook;
use think\Validate;

class User extends Frontend
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

    public function changepwd()
    {
        if ($this->request->isPost()) {
            $oldpassword = $this->request->post("oldpassword", '', null);
            $newpassword = $this->request->post("newpassword", '', null);
            $renewpassword = $this->request->post("renewpassword", '', null);
            $token = $this->request->post('__token__');
            $rule = [
                'oldpassword'   => 'require|regex:\S{6,30}',
                'newpassword'   => 'require|regex:\S{6,30}',
                'renewpassword' => 'require|regex:\S{6,30}|confirm:newpassword',
                '__token__'     => 'token',
            ];

            $msg = [
                'renewpassword.confirm' => __('Password and confirm password don\'t match')
            ];
            $data = [
                'oldpassword'   => $oldpassword,
                'newpassword'   => $newpassword,
                'renewpassword' => $renewpassword,
                '__token__'     => $token,
            ];
            $field = [
                'oldpassword'   => __('Old password'),
                'newpassword'   => __('New password'),
                'renewpassword' => __('Renew password')
            ];
            $validate = new Validate($rule, $msg, $field);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->token()]);
            }

            $ret = $this->auth->changepwd($newpassword, $oldpassword);
            if ($ret) {
                $this->success(__('Reset password successful'), url('user/login'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->token()]);
            }
        }
        $this->view->assign('title', __('Change password'));
        return $this->view->fetch();
    }
}
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

use Throwable;
use badou\Random;
use think\facade\Event;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Validate;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\controller\Frontend;
use app\index\validate\User as UserValidate;

class User extends Frontend
{
    protected $noNeedLogin = ['login', 'register', 'forgetpass'];
    protected $noNeedRight = ['*'];

    public function initialize()
    {
        parent::initialize();
        $auth = $this->auth;

        if (!Config::get('badouadmin.usercenter')) {
            $this->error(__('User center already closed'), '/');
        }

        //监听注册登录退出的事件
        Event::listen('user_login_successed', function ($user) use ($auth) {
            $expire = input('post.keeplogin') ? 30 * 86400 : 0;
            Cookie::set('uid', $user['id'], $expire);
            Cookie::set('token', $auth->getToken(), $expire);
        });
        Event::listen('user_register_successed', function ($user) use ($auth) {
            Cookie::set('uid', $user['id']);
            Cookie::set('token', $auth->getToken());
        });
        Event::listen('user_delete_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
        Event::listen('user_logout_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
    }

    public function index()
    {
        $this->view->assign('title', __('User center'));
        return $this->view->fetch();
    }

    /**
     * 登录
     * @return string
     */
    public function login(): string
    {
        $url = $this->request->request('url', '', 'url_clean');

        if ($this->auth->isLogin()) {
            $this->success(__('You\'ve logged in, do not login again'), $url ?: url('/user/index'));
        }

        if ($this->request->isPost()) {
            $params = $this->request->post(['email', 'mobile', 'username', 'password', 'keeplogin', 'captcha']);

            $validate = new UserValidate();
            try {
                $validate->scene('login')->check($params);
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }

            $res = $this->auth->login($params['username'], $params['password']);

            if (isset($res) && $res === true) {
                /* 增加登录成功事件 */
                Event::trigger('user_login_successed', $this->auth->getUserInfo());
                $this->success(__('Logged in successful'), $url ? $url : url('user/index'));
            } else {
                $msg = $this->auth->getError();
                $this->error($msg);
            }
        }

        //判断来源
        $referer = $this->request->server('HTTP_REFERER', '');
        if (!$url && $referer && !preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Login'));
        return $this->view->fetch('user/login');
    }

    /**
     * 注册
     * @return string
     */
    public function register()
    {
        $url = $this->request->request('url', '', 'url_clean');
        if ($this->auth->id) {
            $this->success(__('You\'ve logged in, do not login again'), $url ? $url : url('user/index'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post(['email', 'mobile', 'username', 'password', 'captcha']);

            $validate = new UserValidate();
            try {
                $validate->scene('register')->check($params);
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }

            $res = $this->auth->register($params['username'], $params['password'], $params['email'], $params['mobile']);

            if (isset($res) && $res === true) {
                Event::trigger('user_register_successed', $this->auth->getUserInfo());

                $this->success(__('Sign up successful'), '', [
                    'userInfo'  => $this->auth->getUserInfo(),
                ]);
            } else {
                $msg = $this->auth->getError();
                $this->error($msg);
            }
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER', '');
        if (!$url && $referer && !preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('captchaType', config('badouadmin.user_register_captcha'));
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Register'));
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
        $this->auth->logout();
        $this->success(__('Logout successful'), url("user/login"));
    }

    // 找回密码
    public function forgetpass()
    {
        $type = $this->request->post("type", "email");
        $mobile = $this->request->post("mobile");
        $email = $this->request->post("email");
        $newpassword = $this->request->post("newpassword");
        $captcha = $this->request->post("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }

        $validate = new UserValidate();
        try {
            $validate->scene('forgetpass')->check(['password' => $newpassword]);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }

        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'forgetpass');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'forgetpass');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'forgetpass');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'forgetpass');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
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

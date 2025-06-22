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
use think\Validate;
use think\facade\Event;
use think\facade\Config;
use think\facade\Cookie;
use app\common\controller\Frontend;
use app\index\validate\User as UserValidate;

class User extends Frontend
{
    protected $noNeedLogin = ['login', 'register', 'third'];
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

    public function login()
    {
        $url = $this->request->get('url', '', 'url_clean');
        $url = $url ?: 'index/index';
        if ($this->auth->isLogin()) {
            $this->success(__("You've logged in, do not login again"), $url);
        }
        //保持会话有效时长，单位:小时
        $keeyloginhours = 24;
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password', '', null);
            $keeplogin = $this->request->post('keeplogin');
            $token = $this->request->post('__token__');
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:3,30',
                '__token__' => 'require|token',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                '__token__' => $token,
            ];
//            if (Config::get('badou.login_captcha')) {
//                $rule['captcha'] = 'require|captcha';
//                $data['captcha'] = $this->request->post('captcha');
//            }
            $validate = new Validate($rule, [], ['username' => __('Username'), 'password' => __('Password'), 'captcha' => __('Captcha')]);
            $result = $validate->check($data);
            if (!$result) {
                $this->error($validate->getError(), $url, ['token' => $this->request->token()]);
            }
//            AdminLog::setTitle(__('Login'));
//            $result = $this->auth->login($username, $password, $keeplogin ? $keeyloginhours * 3600 : 0);
//            if ($result === true) {
//                Hook::listen("admin_login_after", $this->request);
//                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
//            } else {
//                $msg = $this->auth->getError();
//                $msg = $msg ? $msg : __('Username or password is incorrect');
//                $this->error($msg, $url, ['token' => $this->request->token()]);
//            }
        }
//
//        // 根据客户端的cookie,判断是否可以自动登录
//        if ($this->auth->autologin()) {
//            Session::delete("referer");
//            $this->redirect($url);
//        }
//        $background = Config::get('fastadmin.login_background');
//        $background = $background ? (stripos($background, 'http') === 0 ? $background : config('site.cdnurl') . $background) : '';
//        $this->view->assign('keeyloginhours', $keeyloginhours);
//        $this->view->assign('background', $background);
//        $this->view->assign('title', __('Login'));
//        Hook::listen("admin_login_init", $this->request);
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

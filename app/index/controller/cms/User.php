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

namespace app\index\controller\cms;

use ba\Date;
use ba\Random;
use Throwable;
use ba\Captcha;
use ba\ClickCaptcha;
use think\facade\Event;
use think\facade\Config;
use think\facade\Cookie;
use app\admin\model\UserScoreLog;
use app\common\model\UserMoneyLog;
use app\api\validate\User as UserValidate;

class User extends Base
{
    protected array $noNeedLogin = ['login','checkIn', 'logout'];

    protected array $noNeedPermission = [];

    public function initialize(): void
    {
        parent::initialize();
        if (!$this->contentSort['scode']) {
            $this->view->assign('sort', []);
        }
        $auth = $this->auth;
        $this->view->assign('title', __('User center'));

        // 监听用户登录、注册、删除事件
        Event::listen('user_login_successed', function ($user) use ($auth) {
            $expire = request()->post('keeplogin') ? 30 * 86400 : 0;
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
        if ($this->auth->isLogin()) {
            return redirect('/account/overview');
        } else {
            return redirect('/user/login');
        }
    }

    /**
     * 增长统计
     * @return void
     */
    public function accountGrowth()
    {
        $sevenDays = Date::unixTime('day', -6);
        $score     = $money = $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[$i]    = date("Y-m-d", $sevenDays + ($i * 86400));
            $tempToday0  = strtotime($days[$i]);
            $tempToday24 = strtotime('+1 day', $tempToday0) - 1;
            $score[$i]   = UserScoreLog::where('user_id', $this->auth->id)
                ->where('create_time', 'BETWEEN', $tempToday0 . ',' . $tempToday24)
                ->sum('score');

            $userMoneyTemp = UserMoneyLog::where('user_id', $this->auth->id)
                ->where('create_time', 'BETWEEN', $tempToday0 . ',' . $tempToday24)
                ->sum('money');
            $money[$i]     = bcdiv($userMoneyTemp, 100, 2);
        }

        $this->success('', '', [
            'days'  => $days,
            'score' => $score,
            'money' => $money,
        ]);
    }

    /**
     * 登录
     * @return string
     */
    public function login(): string
    {
        $openMemberCenter = Config::get('buildadmin.open_member_center');
        if (!$openMemberCenter) {
            $this->error(__('Member center disabled'));
        }

        $url = $this->request->request('url', '', 'url_clean');
        if ($this->auth->id) {
            $this->success(__('You\'ve logged in, do not login again'), $url ?: url('/user/index'));
        }


        //判断来源
        $referer = $this->request->server('HTTP_REFERER', '', 'url_clean');
        if (!$url && $referer && !preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Login'));
        $this->view->assign('uuid', Random::uuid());
        return $this->view->fetch('user/login');
    }

    /**
     * 会员签入(登录和注册)
     * @throws Throwable
     */
    public function checkIn(): void
    {
        // 检查登录态
        if ($this->auth->isLogin()) {
            $this->success(__('You have already logged in. There is no need to log in again~'), '', [
                'type' => $this->auth::LOGGED_IN
            ]);
        }

        if ($this->request->isPost()) {
            $params = $this->request->post(['tab', 'email', 'mobile', 'username', 'password', 'keep', 'captcha', 'captchaId', 'captchaInfo', 'registerType']);
            if (!in_array($params['tab'], ['login', 'register'])) {
                $this->error(__('Unknown operation'));
            }

            $validate = new UserValidate();
            try {
                $validate->scene($params['tab'])->check($params);
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }

            if ($params['tab'] == 'login') {
                $captchaObj = new ClickCaptcha();
                if (!$captchaObj->check($params['captchaId'], $params['captchaInfo'])) {
                    $this->error(__('Captcha error'));
                }
                $res = $this->auth->login($params['username'], $params['password'], (bool)$params['keep']);
            } elseif ($params['tab'] == 'register') {
                $captchaObj = new Captcha();
                if (!$captchaObj->check($params['captcha'], ($params['registerType'] == 'email' ? $params['email'] : $params['mobile']) . 'user_register')) {
                    $this->error(__('Please enter the correct verification code'));
                }
                $res = $this->auth->register($params['username'], $params['password'], $params['mobile'], $params['email']);
            }

            if (isset($res) && $res === true) {
                if ($params['tab'] == 'login') {
                    /* 增加登录成功事件 */
                    Event::trigger('user_login_successed', $this->auth->getUserInfo());
                } elseif ($params['tab'] == 'register') {
                    /* 增加注册成功事件 */
                    Event::trigger('user_register_successed', $this->auth->getUserInfo());
                }

                $this->success(__('Login succeeded!'), '', [
                    'userInfo'  => $this->auth->getUserInfo(),
                ]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ?: __('Check in failed, please try again or contact the website administrator~');
                $this->error($msg);
            }
        }
    }

    /**
     * 退出
     * @return void
     */
    public function logout(): void
    {
        $this->auth->logout();
        /* 增加退出成功事件 */
        Event::trigger('user_logout_successed', $this->auth->getUserInfo());
        $this->success(__('Logout succeeded~'));
    }

}

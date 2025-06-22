<?php

namespace app\index\controller;

use think\captcha\facade\Captcha;
use app\common\controller\Frontend;

class Index extends Frontend
{
    protected $noNeedLogin = ['*'];
    public function index()
    {
        return $this->view->fetch();
    }

    public function captcha()
    {
        return Captcha::create();
    }
}

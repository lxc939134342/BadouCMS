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

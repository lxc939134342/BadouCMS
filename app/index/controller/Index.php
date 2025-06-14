<?php

namespace app\index\controller;

use app\common\controller\Frontend;

class Index extends Frontend
{
    protected $noNeedLogin = "*";
    public function index()
    {
        return $this->view->fetch();
    }
}

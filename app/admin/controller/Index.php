<?php

namespace app\admin\controller;

use app\common\controller\Backend;

class Index extends Backend
{
    protected $noNeedLogin = ['login'];
    public function index()
    {

    }

    public function login(){
        return $this->view->fetch();
    }
}
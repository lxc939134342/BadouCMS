<?php

namespace app\admin\controller;
use app\common\controller\Backend;
class Dashboard extends Backend
{
    public function index()
    {
        if($this->request->isAjax()){
            dump(111);
        }
        return $this->view->fetch();
    }
}
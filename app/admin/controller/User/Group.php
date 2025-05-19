<?php

namespace app\admin\controller\User;

use app\admin\model\UserGroup;
use app\common\controller\Backend;

class Group  extends Backend
{
    protected $model = null;

    public function initialize()
    {
        parent::initialize();
        $this->model = new UserGroup();
    }

//    public function index()
//    {
//        halt($this->model);
//    }
}
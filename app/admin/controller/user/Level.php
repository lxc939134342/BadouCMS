<?php

namespace app\admin\controller\user;

use Throwable;
use app\common\controller\Backend;

class Level extends Backend
{
    protected $model = null;

    protected $modelValidate = true;

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\UserLevel();
    }
}

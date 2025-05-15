<?php

namespace app\admin\controller\cms;

class Models extends Base
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Models();
    }
    // public function index()
    // {
    //     if ($this->isAjax()) {
    //         $this->success('ok');
    //     }
    //     return $this->view->fetch();
    // }
}

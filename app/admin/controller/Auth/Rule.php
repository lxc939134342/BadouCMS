<?php

namespace app\admin\controller\Auth;

use app\admin\model\AdminRule;
use app\common\controller\Backend;

class Rule extends Backend
{
    public function initialize()
    {
        parent::initialize();

        if (!$this->auth->isSuperAdmin()) {
            $this->error(__('Access is allowed only to the super management group'));
        }
        $this->model=new AdminRule();
    }


    /**
     * 查看
     */
    public function index()
    {
        if ($this->isAjax()) {
            $list=$this->model->withoutField('type,condition,remark,createtime,updatetime')
                    ->order('weigh DESC,id ASC')
                    ->select();
            $total = count($list);
            $this->result('ok',$list,$total);
        }
        return $this->view->fetch();
    }
}
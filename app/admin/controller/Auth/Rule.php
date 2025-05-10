<?php

namespace app\admin\controller\Auth;

use app\admin\model\AdminRule;
use app\common\controller\Backend;
use badou\Tree;

class Rule extends Backend
{
    public function initialize()
    {
        parent::initialize();

        if (!$this->auth->isSuperAdmin()) {
            $this->error(__('Access is allowed only to the super management group'));
        }
        $this->model = new AdminRule();
    }


    /**
     * 查看
     */
    public function index()
    {
        if ($this->isAjax()) {
            $res = $this->model->withoutField('type,condition,remark,create_time,update_time')
                    ->order('weigh DESC,id ASC')
                    ->select()->toArray();
            $total = count($res);
            foreach ($res as &$v) {
                $v['title'] = __($v['title']);
            }
            $treeLib = Tree::instance();
            $list = $treeLib->init($res)->getTreeArray(0);
            $this->result('ok', $list, $total);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        return $this->view->fetch();
    }

    public function edit()
    {
        p($this->request->param());
        return $this->view->fetch();
    }

    public function del()
    {
        $id = $this->request->param('ids');
        p($id);
    }
}

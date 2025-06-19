<?php

namespace app\admin\controller\User;

use app\admin\model\UserGroup;
use app\common\controller\Backend;

class Group extends Backend
{
    protected $model = null;

    public function initialize()
    {
        parent::initialize();
        $this->model = new UserGroup();
    }

    public function add()
    {
        $nodeList = \app\admin\model\UserRule::getTreeList();
        $this->assignconfig("nodeList", $nodeList);
        return parent::add();
    }

    public function edit($ids = null)
    {
        $row = $this->model->find($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $rules = explode(',', $row['rules']);
        $nodeList = \app\admin\model\UserRule::getTreeList($rules);
        $this->assignconfig("nodeList", $nodeList);
        return parent::edit();
    }
}

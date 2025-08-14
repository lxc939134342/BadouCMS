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

namespace app\admin\controller\user;

use app\admin\model\UserGroup;
use app\common\controller\Backend;

class Group extends Backend
{
    protected $model = null;
    protected $modelValidate = true;

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

    public function selectpage()
    {
        return parent::selectpage();
    }
}

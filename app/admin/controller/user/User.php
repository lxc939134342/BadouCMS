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

use app\common\controller\Backend;
use think\facade\Db;

class User extends Backend
{
    protected $model = null;
    protected $modelValidate = true;
    protected $groupList = [];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\User();

        // 必须将结果集转换为数组
        $groupList = Db::name("user_group")
            ->order('id ASC')
            ->column('name', 'id');

        $this->view->assign('groupdata', $groupList);
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model
                ->with(['user_group'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            $this->result('ok', $list->items(), $list->total());
        }
        return $this->view->fetch();
    }

}

<?php

namespace app\admin\controller\User;

use app\common\controller\Backend;
use badou\Tree;
use think\facade\Db;

class User extends Backend
{

    protected $model = null;
    protected $groupList = [];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\User();

        // 必须将结果集转换为数组
        $groupList = Db::name("user_group")
            ->order('id ASC')
            ->column('name','id');

        //数组合并
        $groupList = array_merge([0 => __('None')],$groupList);

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

            $where = [];
            $list = $this->model
                ->with(['user_group'])
                ->where($where)
                ->order('id ASC')
                ->paginate(15);

//            $result = array("total" => $list->total(), "rows" => $list->items());

            $this->result('ok', $list->items(), $list->total());
//            $this->success('ok', null, $result);
        }
        return $this->view->fetch();
    }


    public function add()
    {
        if ($this->request->isPost()) {
            $this->token();
        }
        return parent::add();
    }

}

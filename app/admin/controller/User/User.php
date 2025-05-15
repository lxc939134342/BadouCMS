<?php

namespace app\admin\controller\User;

use app\common\controller\Backend;

class User extends Backend
{
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
                ->with('user_group')
                ->where($where)
                ->order('weigh DESC,id ASC')
                ->paginate(15);

            $result = array("total" => $list->total(), "rows" => $list->items());

            $this->success('ok', null, $result);
        }
        return $this->view->fetch();
    }
}

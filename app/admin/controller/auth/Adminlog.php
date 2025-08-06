<?php

// +----------------------------------------------------------------------
// | BadouAdmin [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://badoucms.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: badouDev <877732602@qq.com>
// +----------------------------------------------------------------------
// | Original reference: https://gitee.com/lande_admin/badouadmin
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// |  日志管理控制器
// +----------------------------------------------------------------------

namespace app\admin\controller\auth;

use app\admin\model\Adminlog as adminlogModel;
use app\common\controller\Backend;

class Adminlog extends Backend
{
    protected $modelClass       = null;
    protected $childrenAdminIds = [];


    public function initialize()
    {
        parent::initialize();
        $this->model = new adminlogModel();

        $this->childrenAdminIds = $this->auth->getChildrenAdminIds(true);
    }

    //删除一个月前的操作日志
    public function deletelog()
    {
        $isAdministrator  = $this->auth->isSuperAdmin();
        $childrenAdminIds = $this->childrenAdminIds;
        $where            = [];
        if (!$isAdministrator) {
            $where[] = ['admin_id', 'in', $childrenAdminIds];
        }
        AdminlogModel::where('create_time', '<= time', time() - (86400 * 30))->where($where)->delete();
        $this->success("删除日志成功！");
    }

    /**
     * 查看
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $isAdministrator                             = $this->auth->isSuperAdmin();
            $childrenAdminIds                            = $this->childrenAdminIds;
            $res                                         = $this->model
                ->where($where)
                ->where(function ($query) use ($isAdministrator, $childrenAdminIds) {
                    if (!$isAdministrator) {
                        $query->where('admin_id', 'in', $childrenAdminIds);
                    }
                })
                ->order($sort, $order)
                ->paginate($limit);
            $this->result('ok', $res->items(), $res->total());
        }
        return $this->fetch();
    }

    /**
     * 详情
     */
    public function detail($id)
    {
        $row = $this->model->find($id);
        if (!$row) {
            $this->error('记录未找到');
        }
        if (!$this->auth->isSuperAdmin()) {
            if (!$row['admin_id'] || !in_array($row['admin_id'], $this->childrenAdminIds)) {
                $this->error('你没有权限访问');
            }
        }
        $this->assign("row", $row->toArray());
        return $this->fetch();
    }

    //添加
    public function add()
    {
        $this->error();
    }

    //编辑
    public function edit()
    {
        $this->error();
    }

    //批量更新
    public function multi()
    {
        // 管理员禁止批量操作
        $this->error();
    }


}

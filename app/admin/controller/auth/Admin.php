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
// |  角色组管理控制器
// +----------------------------------------------------------------------

namespace app\admin\controller\auth;

use app\admin\model\AdminGroupAccess as AuthGroupAccess;
use app\admin\model\AdminGroup as AuthGroupModel;
use app\admin\model\Admin as AdminUserModel;
use app\common\controller\Backend;
use think\exception\ValidateException;
use badou\Tree;
use Exception;
use think\facade\Db;

class Admin extends Backend
{
    protected $searchFields     = 'id,username,nickname';
    protected $childrenGroupIds = [];
    protected $childrenAdminIds = [];
    //无需要权限判断的方法
    protected $noNeedRight = ['getGroupList', 'selectpage'];
    protected $noNeedToken = ['getGroupList'];


    public function initialize()
    {
        parent::initialize();
        $this->model = new AdminUserModel();

        $this->childrenAdminIds = $this->auth->getChildrenAdminIds($this->auth->isSuperAdmin());
        $this->childrenGroupIds = $this->auth->getChildrenGroupIds($this->auth->isSuperAdmin());

        $this->assignconfig("admin", ['id' => $this->auth->id]);
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

            $childrenGroupIds = $this->childrenGroupIds;
            $groupName        = AuthGroupModel::where('id', 'in', $childrenGroupIds)
                ->column('name', 'id');
            $authGroupList = AuthGroupAccess::where('group_id', 'in', $childrenGroupIds)
                ->field('uid,group_id')
                ->select();
            $adminGroupName = [];
            foreach ($authGroupList as $k => $v) {
                if (isset($groupName[$v['group_id']])) {
                    $adminGroupName[$v['uid']][$v['group_id']] = $groupName[$v['group_id']];
                }
            }
            $groups = $this->auth->getGroups();
            foreach ($groups as $m => $n) {
                $adminGroupName[$this->auth->id][$n['id']] = $n['name'];
            }
            list($where, $sort, $order, $offset, $limit, $page, $alias, $bind) = $this->buildparams();

            $list = $this->model
                ->where($where)
                ->where('id', 'in', $this->childrenAdminIds)
                ->withoutField(['password', 'salt', 'token'])
                ->order($sort, $order)
                ->paginate($limit);

            foreach ($list as $k => &$v) {
                $groups           = $adminGroupName[$v['id']] ?? [];
                $v['groups']      = implode(',', array_keys($groups));
                $v['groups_text'] = implode(',', array_values($groups));
            }
            unset($v);
            $this->result('ok', $list->items(), $list->total());
        }
        return $this->fetch();
    }

    /**
     * 添加-创建管理员用户组
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param('row/a');
            Db::startTrans();
            try {
                $this->validate($params, 'AdminUser.insert');
                $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
                $params['avatar']   = '/assets/img/avatar.png'; //设置新管理员默认头像。
                $this->model->save($params);

                $group = explode(',', $params["group"]);
                //过滤不允许的组别,避免越权
                $group = array_intersect($this->childrenGroupIds, $group);
                if (!$group) {
                    throw new Exception('父组别超出权限范围');
                }
                $dataset = [];
                foreach ($group as $value) {
                    $dataset[] = ['uid' => $this->model->id, 'group_id' => $value];
                }
                AuthGroupAccess::insertAll($dataset);

                Db::commit();
            } catch (ValidateException | Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success("添加成功！", url('index'));
        }
        return $this->fetch();
    }


    //编辑管理员用户组
    public function edit()
    {
        $id  = $this->request->param('ids/d', 0);
        $row = $this->model->find($id);
        if (!$row) {
            $this->error('记录未找到');
        }
        if (!in_array($row->id, $this->childrenAdminIds)) {
            $this->error('没有权限操作！');
        }
        if ($this->request->isPost()) {
            $params = $this->request->param('row/a');
            Db::startTrans();
            try {
                $this->validate($params, 'AdminUser.update');
                //密码为空，表示不修改密码
                if (isset($params['password']) && $params['password']) {
                    $params['password'] = $this->auth->getEncryptPassword($params['password']);
                } else {
                    unset($params['password']);
                }

                $row->save($params);

                // 先移除所有权限
                AuthGroupAccess::where('uid', $row->id)->delete();

                $group = explode(',', $params['group']);

                // 过滤不允许的组别,避免越权
                $group = array_intersect($this->childrenGroupIds, $group);
                if (!$group) {
                    throw new Exception('父组别超出权限范围');
                }

                $dataset = [];
                foreach ($group as $value) {
                    $dataset[] = ['uid' => $row->id, 'group_id' => $value];
                }
                AuthGroupAccess::insertAll($dataset);
                Db::commit();
            } catch (ValidateException | Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success("修改成功！");
        }

        $groupIds = $this->auth->getGroupIds($row['id']);
        $this->assign("row", $row);
        $this->view->assign("groupids", implode(',', $groupIds));
        return $this->fetch();
    }

    /**
     * 删除
     */
    public function del()
    {
        if (false === $this->request->isPost()) {
            $this->error('未知参数');
        }
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            $this->error('请指定需要删除的用户ID！');
        }

        $ids = array_intersect($this->childrenAdminIds, array_filter(explode(',', $ids)));

        // 避免越权删除管理员
        $childrenGroupIds = $this->childrenGroupIds;
        $adminList        = $this->model->where('id', 'in', $ids)->where('id', 'in', function ($query) use ($childrenGroupIds) {
            $query->name('admin_group_access')->where('group_id', 'in', $childrenGroupIds)->field('uid');
        })->select();

        if (!$adminList->isEmpty()) {
            $deleteIds = [];
            foreach ($adminList as $k => $v) {
                $deleteIds[] = $v->id;
            }
            $deleteIds = array_values(array_diff($deleteIds, [$this->auth->id]));
            if ($deleteIds) {
                Db::startTrans();
                try {
                    $this->model->destroy($deleteIds);
                    AuthGroupAccess::where('uid', 'in', $deleteIds)->delete();
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                $this->success("删除成功！");
            }
        }
        $this->error('没有权限删除！');
    }

    //批量更新
    public function multi()
    {
        // 管理员禁止批量操作
        $this->error();
    }


    public function getGroupList()
    {
        $keyValue = $this->request->param('keyValue');
        if ($keyValue) {
            $groupList = AuthGroupModel::where('id', 'in', $keyValue)->select()->toArray();
            foreach ($groupList as $k => $v) {
                $groupdata[] = ['id' => $v['id'], 'name' => $v['name']];
            }
            $this->result('ok', ['count' => count($groupdata), 'list' => $groupdata]);
        }
        $groupList = AuthGroupModel::where('id', 'in', $this->childrenGroupIds)->select()->toArray();
        Tree::instance()->init($groupList);
        $groupdata = [];
        if ($this->auth->isSuperAdmin()) {
            $result = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
            foreach ($result as $k => $v) {
                $groupdata[] = ['id' => $v['id'], 'name' => $v['name']];
            }
        } else {
            $groups = $this->auth->getGroups();
            foreach ($groups as $m => $n) {
                $childlist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray($n['id']), 'name');
                foreach ($childlist as $k => $v) {
                    $groupdata[] = ['id' => $v['id'], 'name' => $v['name']];
                }
            }
        }
        $this->result('ok', ['count' => count($groupdata), 'list' => $groupdata]);
    }

    public function selectpage()
    {
        return parent::selectpage();
    }
}

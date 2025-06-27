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
// |  权限管理控制器
// +----------------------------------------------------------------------

namespace app\admin\controller\Auth;

use app\admin\model\AdminGroupAccess as AuthGroupAccess;
use app\admin\model\AdminGroup as AuthGroupModel;
use app\admin\model\AdminRule as AuthRuleModel;
use app\common\controller\Backend;
use badou\Tree;
use Exception;
use think\facade\Db;

class Group extends Backend
{
    protected $rulelist = [];

    //当前登录管理员所有子组别
    protected $childrenGroupIds = [];
    //当前组别列表数据
    protected $grouplist = [];
    protected $groupdata = [];

    //无需要权限判断的方法
    protected $noNeedRight = ['roletree','roletrees'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new AuthGroupModel();

        $this->childrenGroupIds = $this->auth->getChildrenGroupIds(true);

        $groupList = AuthGroupModel::where('id', 'in', $this->childrenGroupIds)->select()->toArray();

        Tree::instance()->init($groupList);
        $groupList = [];
        if ($this->auth->isSuperAdmin()) {
            $groupList = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
        } else {
            $groups   = $this->auth->getGroups();
            $groupIds = [];
            foreach ($groups as $m => $n) {
                if (in_array($n['id'], $groupIds) || in_array($n['pid'], $groupIds)) {
                    continue;
                }
                $groupList = array_merge($groupList, Tree::instance()->getTreeList(Tree::instance()->getTreeArray($n['pid']), 'name'));
                foreach ($groupList as $index => $item) {
                    $groupIds[] = $item['id'];
                }
            }
        }

        $groupName = [];
        foreach ($groupList as $k => $v) {
            $groupName[$v['id']] = $v['name'];
        }
        $this->grouplist = $groupList;
        $this->groupdata = $groupName;
        $this->assignconfig("admin", ['id' => $this->auth->id, 'group_ids' => $this->auth->getGroupIds()]);
        $this->assign('groupdata', $this->groupdata);
    }

    /**
     * 查看
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $list   = $this->grouplist;
            $total  = count($list);
            $this->result('ok', $list, $total);
        } else {
            return $this->view->fetch();
        }
    }

    /**
     * 添加-创建管理员用户组
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a", [], 'strip_tags');
            $params['rules'] = explode(',', $params['rules']);
            if (!in_array($params['pid'], $this->childrenGroupIds)) {
                $this->error(__('The parent group exceeds permission limit'));
            }
            $parentmodel = AuthGroupModel::find($params['pid']);
            if (!$parentmodel) {
                $this->error(__('The parent group can not found'));
            }
            // 父级别的规则节点
            $parentrules = explode(',', $parentmodel->rules);
            // 当前组别的规则节点
            $currentrules = $this->auth->getRuleIds();
            $rules = $params['rules'];

            // 如果父组不是超级管理员则需要过滤规则节点,不能超过父组别的权限
            $rules = in_array('*', $parentrules) ? $rules : array_intersect($parentrules, $rules);

            // 如果当前组别不是超级管理员则需要过滤规则节点,不能超当前组别的权限
            $rules = in_array('*', $currentrules) ? $rules : array_intersect($currentrules, $rules);
            $params['rules'] = implode(',', $rules);
            if ($params) {
                $this->model->create($params);
                $this->success();
            }
            $this->error();
        }
        return $this->fetch();

    }


    //编辑管理员用户组
    public function edit($ids = null)
    {
        if (!in_array($ids, $this->childrenGroupIds)) {
            $this->error(__('You have no permission'));
        }
        $row = $this->model->find(['id' => $ids]);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            $this->token();
            $AuthGroupModel = new AuthGroupModel();
            $params = $this->request->post("row/a", [], 'strip_tags');
            //父节点不能是非权限内节点
            if (!in_array($params['pid'], $this->childrenGroupIds)) {
                $this->error(__('The parent group exceeds permission limit'));
            }
            // 父节点不能是它自身的子节点或自己本身
            if (in_array($params['pid'], Tree::instance()->getChildrenIds($row->id, true))) {
                $this->error(__('The parent group can not be its own child or itself'));
            }
            $params['rules'] = explode(',', $params['rules']);

            $parentmodel = $AuthGroupModel::where('id', $params['pid'])->find();
            if (!$parentmodel) {
                $this->error(__('The parent group can not found'));
            }
            // 父级别的规则节点
            $parentrules = explode(',', $parentmodel->rules);
            // 当前组别的规则节点
            $currentrules = $this->auth->getRuleIds();
            $rules = $params['rules'];

            // 如果父组不是超级管理员则需要过滤规则节点,不能超过父组别的权限
            $rules = in_array('*', $parentrules) ? $rules : array_intersect($parentrules, $rules);

            // 如果当前组别不是超级管理员则需要过滤规则节点,不能超当前组别的权限
            $rules = in_array('*', $currentrules) ? $rules : array_intersect($currentrules, $rules);

            $params['rules'] = implode(',', $rules);

            if ($params) {
                Db::startTrans();
                try {
                    $row->save($params);
                    $ids = Tree::instance()->getChildrenIds($row->id);

                    $children_auth_groups = AuthGroupModel::where('id', 'in', $ids)->select();


                    $childparams = [];
                    foreach ($children_auth_groups as $key => $children_auth_group) {
                        $childparams[$key]['id'] = $children_auth_group->id;
                        $childparams[$key]['rules'] = implode(',', array_intersect(explode(',', $children_auth_group->rules), $rules));
                    }
                    $AuthGroupModel->saveAll($childparams);
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }

                $this->success();
            }
            $this->error();
            return;
        }
        $this->view->assign("row", $row);
        return $this->fetch();
    }

    /**
     * 删除
     */
    public function del()
    {
        $ids = $this->request->param('ids');
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        if ($ids) {
            $grouplist = $this->auth->getGroups();
            $group_ids = array_map(function ($group) {
                return $group['id'];
            }, $grouplist);
            // 移除掉当前管理员所在组别
            $ids = array_diff($ids, $group_ids);
            // 循环判断每一个组别是否可删除
            $grouplist = $this->model->where('id', 'in', $ids)->select()->toArray();
            foreach ($grouplist as $k => $v) {
                // 当前组别下有管理员
                $groupone = AuthGroupAccess::where(['group_id' => $v['id']])->find();
                if ($groupone) {
                    $ids = array_diff($ids, [$v['id']]);
                    continue;
                }
                // 当前组别下有子组别
                $groupone = $this->model->where(['pid' => $v['id']])->find();
                if ($groupone) {
                    $ids = array_diff($ids, [$v['id']]);
                    continue;
                }
            }
            if (!$ids) {
                $this->error('你不能删除含有子组和管理员的组');
            }
            $count = $this->model->where('id', 'in', $ids)->delete();
            if ($count) {
                $this->success();
            }
        }
        $this->error();
    }

    /**
     * 读取角色权限树
     *
     * @internal
     */
    public function roletree()
    {
        $this->loadlang('auth/rule', $this->app->lang->getLangSet());

        $model = new AuthGroupModel();
        $id = $this->request->post("id");
        $pid = $this->request->post("pid");
        $parentGroupModel = $model->find($pid);
        $currentGroupModel = null;
        if ($id) {
            $currentGroupModel = $model->find($id);
        }
        if (($pid || $parentGroupModel) && (!$id || $currentGroupModel)) {
            $id = $id ? $id : null;
            $ruleList = AuthRuleModel::order('weigh', 'desc')
                ->order('id', 'asc')
                ->select()->toArray();

            //读取父类角色所有节点列表
            $parentRuleList = [];
            if (in_array('*', explode(',', $parentGroupModel->rules))) {
                $parentRuleList = $ruleList;
            } else {
                $parentRuleIds = explode(',', $parentGroupModel->rules);
                foreach ($ruleList as $k => $v) {
                    if (in_array($v['id'], $parentRuleIds)) {
                        $parentRuleList[] = $v;
                    }
                }
            }

            $ruleTree = new Tree();
            $groupTree = new Tree();
            //当前所有正常规则列表
            $ruleTree->init($parentRuleList);
            //角色组列表
            $groupTree->init($model->where('id', 'in', $this->childrenGroupIds)->select()->toArray());

            //读取当前角色下规则ID集合
            $adminRuleIds = $this->auth->getRuleIds();
            //是否是超级管理员
            $superadmin = $this->auth->isSuperAdmin();
            //当前拥有的规则ID集合
            $currentRuleIds = $id ? explode(',', $currentGroupModel->rules) : [];

            if (!$id || !in_array($pid, $this->childrenGroupIds) || !in_array($pid, $groupTree->getChildrenIds($id, true))) {
                $parentRuleList = $ruleTree->getTreeList($ruleTree->getTreeArray(0), 'name');
                $hasChildrens = [];
                foreach ($parentRuleList as $k => $v) {
                    if ($v['haschild']) {
                        $hasChildrens[] = $v['id'];
                    }
                }
                $parentRuleIds = array_map(function ($item) {
                    return $item['id'];
                }, $parentRuleList);
                $nodeList = [];
                foreach ($parentRuleList as $k => $v) {
                    if (!$superadmin && !in_array($v['id'], $adminRuleIds)) {
                        continue;
                    }
                    if ($v['pid'] && !in_array($v['pid'], $parentRuleIds)) {
                        continue;
                    }
                    $state = array('selected' => in_array($v['id'], $currentRuleIds) && !in_array($v['id'], $hasChildrens));
                    $nodeList[] = array('id' => $v['id'], 'parent' => $v['pid'] ? $v['pid'] : '#', 'text' => __($v['title']), 'type' => 'menu', 'state' => $state);
                }
                $this->success('', null, $nodeList);
            } else {
                $this->error(__('Can not change the parent to child'));
            }
        } else {
            $this->error(__('Group not found'));
        }
    }

    //批量更新
    public function multi()
    {
        // 管理员禁止批量操作
        $this->error();
    }
}

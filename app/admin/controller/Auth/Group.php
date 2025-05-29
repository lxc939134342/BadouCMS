<?php

// +----------------------------------------------------------------------
// | BadouAdmin [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://badouadmin.com All rights reserved.
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
use think\exception\ValidateException;
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

        // p($groupList);

        Tree::instance()->init($groupList);
        $groupList = [];
        if ($this->auth->isSuperAdmin()) {
            $groupList = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
        } else {
            $groups   = $this->auth->getGroups();
            $groupIds = [];
            foreach ($groups as $m => $n) {
                if (in_array($n['id'], $groupIds) || in_array($n['parentid'], $groupIds)) {
                    continue;
                }
                $groupList = array_merge($groupList, Tree::instance()->getTreeList(Tree::instance()->getTreeArray($n['parentid']), 'name'));
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
        // View::assign( $this->groupdata);

    }

    /**
     * 查看
     */
    public function index()
    {
        // if ($this->isAjax()) {
        //     $res = $this->model
        //             ->withoutField('type,condition,remark,create_time,update_time')
        //             ->order('weigh DESC,id ASC')
        //             ->select()->toArray();
        //     $total = count($res);
        //     foreach ($res as &$v) {
        //         $v['title'] = __($v['title']);
        //     }
        //     $treeLib = Tree::instance();
        //     $list = $treeLib->init($res)->getTreeArray(0);
        //     $this->result('ok', $list, $total);
        // }
        if ($this->request->isAjax()) {
            $list   = $this->grouplist;
            $total  = count($list);
            // $result = ["code" => 0, "count" => $total, "data" => $list];
            // return json($result);
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
            $this->token();
            $params = $this->request->post("row/a", [], 'strip_tags');
            try {
                $this->validate($params, 'app\admin\validate\AuthGroup');
            } catch (ValidateException $e) {
                $this->error($e->getMessage());
            }
            if (!in_array($params['parentid'], $this->childrenGroupIds)) {
                $this->error('父组别超出权限范围');
            }
            $parentmodel = AuthGroupModel::find($params['parentid']);
            if (!$parentmodel) {
                $this->error('父组别未找到');
            }
            if ($params) {
                $this->model->create($params);
                $this->success('新增成功');
            }
            $this->error('参数不能为空');
        }
        return $this->fetch();

    }


    //编辑管理员用户组
    public function edit()
    {
        $id = $this->request->param('id/d');
        if (!in_array($id, $this->childrenGroupIds)) {
            $this->error('你没有权限访问!');
        }
        $row = $this->model->find($id);
        if (!$row) {
            $this->error('记录未找到');
        }
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a", [], 'strip_tags');
            //父节点不能是非权限内节点
            if (!in_array($params['parentid'], $this->childrenGroupIds)) {
                $this->error('父组别超出权限范围');
            }
            // 父节点不能是它自身的子节点或自己本身
            if (in_array($params['parentid'], Tree::instance()->getChildrenIds($row->id, true))) {
                $this->error('父角色不能是自身！');
            }
            $params['rules'] = explode(',', $params['rules']);

            $parentmodel = AuthGroupModel::find($params['parentid']);
            if (!$parentmodel) {
                $this->error('父组别未找到');
            }

            // 父级别的规则节点
            $parentrules = explode(',', $parentmodel->rules);
            // 当前组别的规则节点
            $currentrules = $this->auth->getRuleIds();
            $rules        = $params['rules'];
            // 如果父组不是超级管理员则需要过滤规则节点,不能超过父组别的权限
            $rules = in_array('*', $parentrules) ? $rules : array_intersect($parentrules, $rules);
            // 如果当前组别不是超级管理员则需要过滤规则节点,不能超当前组别的权限
            $rules           = in_array('*', $currentrules) ? $rules : array_intersect($currentrules, $rules);
            $params['rules'] = implode(',', $rules);
            if ($params) {
                Db::startTrans();
                try {
                    $row->save($params);
                    $children_auth_groups = $this->model->whereIn('id', implode(',', (Tree::instance()->getChildrenIds($row->id))))->select();
                    $childparams          = [];
                    foreach ($children_auth_groups as $key => $children_auth_group) {
                        $childparams[$key]['id']    = $children_auth_group->id;
                        $childparams[$key]['rules'] = implode(',', array_intersect(explode(',', $children_auth_group->rules), $rules));
                    }
                    $this->model->saveAll($childparams);
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                $this->success('编辑成功');
            }
            $this->error('参数不能为空');
        }
        $this->assign("data", $row);
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
        $ids = $this->request->param('id/a', null);
        if (empty($ids)) {
            $this->error('参数错误！');
        }
        if (!is_array($ids)) {
            $ids = [0 => $ids];
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
                $groupone = $this->model->where(['parentid' => $v['id']])->find();
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

        $model             = new AuthGroupModel();
        $id                = $this->request->post("id");
        $pid               = $this->request->post("parentid");
        $parentGroupModel  = $model->find($pid);
        $currentGroupModel = null;
        if ($id) {
            $currentGroupModel = $model->find($id);
        }
        if (($pid || $parentGroupModel) && (!$id || $currentGroupModel)) {
            $id       = $id ? $id : null;
            $ruleList = AuthRuleModel::order('listorder', 'desc')->order('id', 'asc')->select()->toArray();

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
            $ruleTree  = new Tree();
            $groupTree = new Tree();
            //当前所有正常规则列表
            $ruleTree->init($parentRuleList);
            //角色组列表
            $groupTree->init(AuthGroupModel::where('id', 'in', $this->childrenGroupIds)->select()->toArray());

            //读取当前角色下规则ID集合
            $adminRuleIds = $this->auth->getRuleIds();
            //是否是超级管理员
            $superadmin = $this->auth->isSuperAdmin();
            //当前拥有的规则ID集合
            $currentRuleIds = $id ? explode(',', $currentGroupModel->rules) : [];

            if (!$id || !in_array($pid, $this->childrenGroupIds) || !in_array($pid, $groupTree->getChildrenIds($id, true))) {
                $parentRuleList = $ruleTree->getTreeList($ruleTree->getTreeArray(0), 'name');
                $hasChildrens   = [];
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
                    if ($v['parentid'] && !in_array($v['parentid'], $parentRuleIds)) {
                        continue;
                    }
                    $state      = ['selected' => in_array($v['id'], $currentRuleIds) && !in_array($v['id'], $hasChildrens)];
                    $nodeList[] = ['id' => $v['id'], 'parent' => $v['parentid'] ? $v['parentid'] : '#', 'text' => $v['title'], 'type' => 'menu', 'state' => $state];
                }
                $this->success('', null, $nodeList);
            } else {
                $this->error('父组别不能是它的子组别');
            }
        } else {
            $this->error('组别未找到');
        }
    }

    public function roletrees()
    {
        $nodeList = [
            [
                "id" => 1,
                "parent" => "#",
                "text" => "常规管理",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 10,
                "parent" => 1,
                "text" => "配置管理",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 23,
                "parent" => 10,
                "text" => "新增",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 24,
                "parent" => 10,
                "text" => "编辑",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 25,
                "parent" => 10,
                "text" => "删除",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 26,
                "parent" => 10,
                "text" => "批量更新",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 27,
                "parent" => 10,
                "text" => "查看",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 11,
                "parent" => 1,
                "text" => "网站设置",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 13,
                "parent" => 1,
                "text" => "附件管理",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 14,
                "parent" => 13,
                "text" => "查看",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 15,
                "parent" => 13,
                "text" => "删除",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 16,
                "parent" => 13,
                "text" => "图片本地化",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 17,
                "parent" => 13,
                "text" => "图片选择",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 4,
                "parent" => 1,
                "text" => "个人资料",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 5,
                "parent" => 4,
                "text" => "查看",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 6,
                "parent" => 4,
                "text" => "资料更新",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 28,
                "parent" => "#",
                "text" => "权限管理",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 29,
                "parent" => 28,
                "text" => "管理员管理",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 30,
                "parent" => 29,
                "text" => "查看",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 31,
                "parent" => 29,
                "text" => "编辑",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 32,
                "parent" => 29,
                "text" => "删除",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 33,
                "parent" => 29,
                "text" => "新增",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 34,
                "parent" => 28,
                "text" => "管理日志",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 35,
                "parent" => 34,
                "text" => "查看",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 36,
                "parent" => 34,
                "text" => "删除",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 37,
                "parent" => 34,
                "text" => "详情",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 38,
                "parent" => 28,
                "text" => "角色管理",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 41,
                "parent" => 38,
                "text" => "查看",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 42,
                "parent" => 38,
                "text" => "新增",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 43,
                "parent" => 38,
                "text" => "编辑",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 44,
                "parent" => 38,
                "text" => "删除",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 12,
                "parent" => 28,
                "text" => "菜单管理",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 18,
                "parent" => 12,
                "text" => "查看",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 19,
                "parent" => 12,
                "text" => "新增",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 20,
                "parent" => 12,
                "text" => "编辑",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 21,
                "parent" => 12,
                "text" => "删除",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 22,
                "parent" => 12,
                "text" => "批量更新",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 50,
                "parent" => "#",
                "text" => "会员管理",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 51,
                "parent" => 50,
                "text" => "会员管理",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 52,
                "parent" => 51,
                "text" => "查看",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 53,
                "parent" => 51,
                "text" => "新增",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 54,
                "parent" => 51,
                "text" => "编辑",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 55,
                "parent" => 51,
                "text" => "删除",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 56,
                "parent" => 51,
                "text" => "审核",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 57,
                "parent" => 50,
                "text" => "审核会员",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 58,
                "parent" => 50,
                "text" => "会员组管理",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 59,
                "parent" => 58,
                "text" => "查看",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 60,
                "parent" => 58,
                "text" => "新增",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 61,
                "parent" => 58,
                "text" => "编辑",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 62,
                "parent" => 58,
                "text" => "删除",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 63,
                "parent" => 50,
                "text" => "VIP等级管理",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 64,
                "parent" => 63,
                "text" => "查看",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 65,
                "parent" => 63,
                "text" => "新增",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 66,
                "parent" => 63,
                "text" => "编辑",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 67,
                "parent" => 63,
                "text" => "删除",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 68,
                "parent" => 63,
                "text" => "批量更新",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 2,
                "parent" => "#",
                "text" => "插件管理",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 45,
                "parent" => 2,
                "text" => "查看",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 46,
                "parent" => 2,
                "text" => "配置",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ],
            [
                "id" => 49,
                "parent" => 2,
                "text" => "禁用启用",
                "type" => "menu",
                "state" => [
                    "selected" => false
                ]
            ]
        ];
        $this->success('', null, $nodeList);

    }



    //批量更新
    public function multi()
    {
        // 管理员禁止批量操作
        $this->error();
    }






    // public function add()
    // {
    //     if ($this->isAjax()) {
    //         parent::add();
    //     }
    //     return $this->view->fetch();
    // }

    // public function selectpage()
    // {
    //     return parent::selectpage();
    // }


}

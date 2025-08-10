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

use app\admin\model\UserRule;
use app\common\controller\Backend;
use badou\Tree;
use think\facade\Db;

class Rule extends Backend
{
    protected $model = null;
    protected $modelValidate = true;
    protected $rulelist = [];
    public function initialize()
    {
        parent::initialize();
        $this->model = new UserRule();


        // 必须将结果集转换为数组
        $ruleList = Db::name("user_rule")
            ->withoutField('type,condition,remark,createtime,updatetime')
            ->order('weigh DESC,id ASC')
            ->select()->toArray();
        foreach ($ruleList as $k => &$v) {
            $v['title'] = __($v['title']);
        }
        unset($v);
        Tree::instance()->init($ruleList);
        $this->rulelist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'title');
        $ruledata = [0 => __('None')];
        foreach ($this->rulelist as $k => &$v) {
            if (!$v['ismenu']) {
                continue;
            }
            $ruledata[$v['id']] = $v['title'];
            unset($v['spacer']);
        }
        unset($v);
        $this->view->assign('ruledata', $ruledata);
    }


    public function index()
    {
        if ($this->isAjax()) {
            $res = $this->model
                ->withoutField('remark,create_time,update_time')
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

    public function selectpage()
    {
        return parent::selectpage();
    }
}

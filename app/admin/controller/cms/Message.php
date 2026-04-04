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

namespace app\admin\controller\cms;

use app\common\controller\Backend;

/**
 * 留言信息
 */
class Message extends Backend
{
    /**
     * Message模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\Message
     */
    protected $model;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Message();

        /* 获取表单字段 */
        $formFieldsModel = new \app\admin\model\cms\FormField();
        $formFields = $formFieldsModel->where('fcode', 1)->select();
        if ($formFields) {
            $formFields = $formFields->toArray();
        }
        $this->view->assign('formFields', $formFields);
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */

    /**
     * 查看
     */
    public function index()
    {
        if ($this->request->param('select')) {
            $this->select();
        }
        if ($this->isAjax()) {
            list($where, $sort, $order, $offset, $limit, $page, $alias, $bind) = $this->buildparams();

            $res = $this->model
                ->withJoin($this->withJoinTable, $this->withJoinType)
                ->alias($alias)
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            $this->result('ok', $res->items(), $res->total());
        }

        return $this->view->fetch();
    }
}

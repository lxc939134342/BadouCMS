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
 * 文章评论管理
 */
class MemberComment extends Backend
{
    /**
     * MemberComment模型对象
     * @var \app\admin\model\cms\MemberComment
     */
    protected $model;

    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\MemberComment();

    }

    public function index()
    {
        if (!$this->request->isAjax()) {
            return $this->view->fetch();
        }

        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $sort, $order, $offset, $limit, $page, $alias, $bind) = $this->buildparams();
        $where[] = [
            'acode','=',get_backend_lang()
        ];
        $withJoinTable = [
            'content' => function ($query) {
                $query->field('title');
            },
            'user' => function ($query) {
                $query->field('user.nickname');
            },
            'puser' => function ($query) {
                $query->field('puser.nickname');
            },
        ];
        $res = $this->model
            ->withJoin($withJoinTable, 'LEFT')
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $this->result('', $res->items(), $res->total());
    }

    /**
     * 批量审核/禁用
     */
    public function review()
    {
        $ids = $this->request->param('ids');
        $status = $this->request->param('status');
        if (empty($ids)) {
            return $this->error(__('Please select the comment to be reviewed'));
        }
        $this->model->whereIn('id', $ids)->update(['status' => $status]);
        if ($status == 1) {
            return $this->success(__('Review success'));
        } else {
            return $this->success(__('Disable success'));
        }
    }
}

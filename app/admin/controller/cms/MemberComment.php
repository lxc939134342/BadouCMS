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
     * @var object
     * @phpstan-var \app\admin\model\cms\MemberComment
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\MemberComment();
        $this->withJoinTable = [
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

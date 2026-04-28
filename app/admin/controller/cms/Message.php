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



/**
 * 留言信息
 */
class Message extends Base
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
        $this->assignconfig('formFields', $formFields);
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

        $this->assignHook('index');
        return $this->view->fetch();
    }
    public function del($ids = '')
    {
        $where = [];
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$this->dataLimitField, 'in', $dataLimitAdminIds];
        }

        $ids = $this->request->param('ids');

        // 触发观察者 - 删除前
        $res = $this->triggerObserver('BeforeDel', $ids, $this);
        if (is_array($res)) {
            $ids = $res;
        }

        $where[] = [$this->pk, 'in', $ids];

        $list = $this->model->where($where)->select();
        $count = 0;
        $this->model->startTrans();
        try {
            foreach ($list as $k => $v) {
                $count += $v->delete();
            }
            $this->model->commit();
        } catch (\Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success(__('Delete successful'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }
}

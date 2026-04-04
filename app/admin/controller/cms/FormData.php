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

use Throwable;

/**
 * 自定义表单-数据
 */
class FormData extends Base
{
    /**
     * FormData模型对象
     * @var \app\admin\model\cms\FormData
     */
    protected $model;

    protected $formModel;

    protected $fcode;

    protected $table_name;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\FormData();
        $this->formModel = new \app\admin\model\cms\Form();
        $this->fcode = $this->request->param('fcode', 0);
        $this->view->assign('fcode', $this->fcode);
        $form =  $this->formModel->where('fcode', $this->fcode)->find();
        /* 表单不存在 */
        if (!$form) {
            $this->error(__('Form does not exist'));
        }
        $this->model = $this->model->name($form['table_name']);
    }

    /**
     * 查看
     * @throws Throwable
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

        /* 获取表单字段 */
        $formFieldsModel = new \app\admin\model\cms\FormField();
        $formFields = $formFieldsModel->where('fcode', $this->fcode)->select();

        $this->assignconfig('formFields', $formFields);
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
        $where[] = [$this->pk, 'in', $ids];

        $this->model->startTrans();
        try {
            // 直接用查询构造器指明表名
            $tableName = $this->model->getTable(); // 动态获取当前实际表名
            $count = \think\facade\Db::table($tableName)->where($where)->delete();
            $this->model->commit();
        } catch (Throwable $e) {
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

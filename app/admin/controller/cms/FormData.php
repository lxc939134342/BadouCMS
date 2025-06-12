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

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\FormData();
        $this->formModel = new \app\admin\model\cms\Form();
        $this->fcode = $this->request->param('fcode', 0);
        $this->view->assign('fcode', $this->fcode);
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
            $form =  $this->formModel->where('fcode', $this->fcode)->find();
            /* 表单不存在 */
            if (!$form) {
                $this->error(__('Form does not exist'));
            }

            $where[] = [
                'acode', '=', get_backend_lang()
            ];

            $res = $this->model->table($form['table_name'])
                ->withJoin($this->withJoinTable, $this->withJoinType)
                ->alias($alias)
                ->where($where)
                ->order($sort, $order, )
                ->paginate($limit);

            $this->result('ok', $res->items(), $res->total());
        }

        /* 获取表单字段 */
        $formFieldsModel = new \app\admin\model\cms\FormField();
        $formFields = $formFieldsModel->where('fcode', $this->fcode)->select();

        $this->assignconfig('formFields', $formFields);


        return $this->view->fetch();
    }
}

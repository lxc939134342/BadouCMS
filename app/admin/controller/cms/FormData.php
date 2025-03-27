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
     * @var object
     * @phpstan-var \app\admin\model\cms\FormData
     */
    protected object $model;

    protected object $formModel;

    protected array|string $preExcludeFields = ['id', 'create_time'];

    protected string|array $quickSearchField = ['id'];

    protected int $fcode;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\FormData();
        $this->formModel = new \app\admin\model\cms\Form();
        $this->fcode = $this->request->param('fcode') ?? 0;
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $get = $this->request->get();
        $form =  $this->formModel->where('fcode', $get['fcode'])->find();
        /* 表单不存在 */
        if (!$form) {
            $this->error(__('Form does not exist'));
        }
        /* 获取表单字段 */
        $formFieldsModel = new \app\admin\model\cms\FormField();
        $formFields = $formFieldsModel->where('fcode', $get['fcode'])->select();

        $where[] = [
            'acode', '=', get_backend_lang()
        ];

        $res = $this->model->table($form['table_name'])
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);
        $this->success('', [
            'formFields' => $formFields,
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }



}

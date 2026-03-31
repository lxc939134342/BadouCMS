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
 * 自定义表单-字段
 */
class FormField extends Base
{
    /**
     * Field模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\FormField
     */
    protected $model;

    protected $formModel;
    protected $fcode;


    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\FormField();
        $this->formModel = new \app\admin\model\cms\Form();
        $this->fcode = $this->request->param('fcode', 1);
        $this->view->assign('fcode', $this->fcode);
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index()
    {

        if ($this->isAjax()) {
            list($where, $sort, $order, $offset, $limit, $page, $alias, $bind) = $this->buildparams();
            if ($this->fcode) {
                $where[] = [
                    'fcode',
                    '=',
                    $this->fcode
                ];
            }
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

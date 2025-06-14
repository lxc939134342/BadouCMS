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
 * 文章内链
 */
class Tags extends Base
{
    /**
     * Tags模型对象
     * @var  \app\admin\model\cms\Tags
     */
    protected $model;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Tags();
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
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

        $res = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $this->result('', $res->items(), $res->total());
    }
    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $post = $this->getOriginalInputData('row/a');
            $post['acode'] = get_backend_lang();
            $post['create_user'] = $this->auth->username;
            $post['update_user'] = $this->auth->username;

            $this->request->withPost(['row' => $post]);
            parent::add();
        }
        return $this->view->fetch();
    }

}

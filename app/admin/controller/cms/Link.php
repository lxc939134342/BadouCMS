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
 * 轮播图片
 */
class Link extends Base
{
    /**
     * Link模型对象
     * @var \app\admin\model\cms\Link
     */
    protected $model;


    protected string $weighField = 'sorting';

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Link();
        $this->assign('gidList', $this->model->getGidList(get_backend_lang()));
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

        /* 查询子栏目数据 */
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
            $post = $this->getPostData('row/a', true);
            // 构建数据
            $default = [
               'acode' => get_backend_lang(),
               'gid' => 0,
               'pic' => '',
               'link' => '',
               'title' => '',
               'subtitle' => '',
               'sorting' => 255,
               'create_user' => $this->auth->username,
               'update_user' => $this->auth->username
            ];
            $post = array_merge($default, $post);

            $LinkModel = $this->model;
            if ($this->request->post('gid') == 0) {
                $gid = $LinkModel->where('acode', get_backend_lang())->order('gid', 'desc')->value('gid');
                $post['gid'] = $gid + 1;
            }
            $newPost['row'] = $post;
            $this->request->withPost($newPost);

            parent::add();
        }
        return $this->view->fetch();
    }

}

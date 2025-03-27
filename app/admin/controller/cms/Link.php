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
 * 友情链接
 */
class Link extends Backend
{
    /**
     * Link模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\Link
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected string|array $quickSearchField = ['id','name'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Link();
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */

    /**
     * 添加
     * @return void
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            //            var_dump($post);die();
            $post['acode'] = get_backend_lang();
            $post['create_user'] = 'admin';
            $post['update_user'] = 'admin';

            $linkModel = $this->model;
            if ($this->request->post('gid') == 0) {
                $gid = $linkModel->where('acode', 'cn')->order('gid', 'desc')->value('gid');
                $post['gid'] = $gid + 1;
            }
            $this->request->withPost($post);

            //            var_dump($this->request->post());die();
            parent::add();
        }

        $linkModel = $this->model;
        $res = $linkModel->where('acode', 'cn')->distinct(true)->field('gid')->order('gid', 'asc')->select();

        $addRes['gid_text'] = '自动新增分组';
        $addRes['gid'] = 0;

        $res->push($addRes);
        // var_dump($res);die();
        $data = [
            'list' => $res,
            'remark' => ''
        ];
        $this->success('ok', $data);
    }


}

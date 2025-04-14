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
class Link extends Base
{
    /**
     * Link模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\Link
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected string|array $quickSearchField = ['id','name'];

    protected string $weighField = 'sorting';

    protected string|array $defaultSortField = ['sorting' => 'asc'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Link();
    }

    /**
     * 添加
     * @return void
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $post = $this->getPostData();

            // 构建数据
            $default = [
                'acode' => get_backend_lang(),
                'gid' => 0,
                'name' => '',
                'link' => '',
                'logo' => '',
                'sorting' => 255,
                'create_user' =>  $this->auth->username,
                'update_user' =>  $this->auth->username
            ];
            $post = array_merge($default, $post);

            $linkModel = $this->model;
            if ($this->request->post('gid') == 0) {
                $gid = $linkModel->where('acode', 'cn')->order('gid', 'desc')->value('gid');
                $post['gid'] = $gid + 1;
            }
            $this->request->withPost($post);
            parent::add();
        }

        $linkModel = $this->model;
        $res = $linkModel->where('acode', get_backend_lang())
            ->distinct(true)
            ->field('gid')
            ->order('gid', 'asc')->select();

        $addRes['gid_text'] = '自动新增分组';
        $addRes['gid'] = 0;

        $res->push($addRes);
        $data = [
            'list' => $res,
            'remark' => ''
        ];
        $this->success('ok', $data);
    }
}

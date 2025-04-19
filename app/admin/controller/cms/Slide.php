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
class Slide extends Base
{
    /**
     * Slide模型对象
     * @var \app\admin\model\cms\Slide
     * @phpstan-var \app\admin\model\cms\Slide
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected string|array $quickSearchField = ['id','title'];

    protected string $weighField = 'sorting';

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Slide();
    }


    /**
     * 添加
     * @return void
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $post = $this->getPostData(true);
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

            $slideModel = $this->model;
            if ($this->request->post('gid') == 0) {
                $gid = $slideModel->where('acode', 'cn')->order('gid', 'desc')->value('gid');
                $post['gid'] = $gid + 1;
            }
            $this->request->withPost($post);

            parent::add();
        }

        $slideModel = $this->model;
        $res = $slideModel
            ->where('acode', get_backend_lang())
                ->distinct(true)
                ->field('gid')
                ->order('gid', 'asc')
                ->select();

        $addRes['gid_text'] = '自动新增分组';
        $addRes['gid'] = 0;

        $res->push($addRes);
        $data = [
            'list' => $res,
            'remark' => ''
        ];
        $this->success('ok', $data);
    }



    /**
     * 获取分组
     * @return void
     */
    public function getGid(): void
    {
        $slideModel = $this->model;
        $res = $slideModel->where('acode', 'cn')->distinct(true)->field('gid')->order('gid', 'asc')->select();
        $this->success('', [
            'gids'   => $res,
        ]);
    }

}

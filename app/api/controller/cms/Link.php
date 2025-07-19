<?php

namespace app\api\controller\cms;

class Link extends Base
{
    protected array $noNeedLogin = ['*'];
    protected $model = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\index\model\cms\Link();
    }

    public function index()
    {
        // 获取参数
        $gid = $this->request->param('gid');
        $num = $this->request->param('num', 5);
        if (!!$gid) {
            $data = $this->model::linkList($gid, $num);
            $this->success('获取成功', $data);
        } else {
            $this->error('必须传递幻灯片分组gid参数');
        }
    }
}

<?php

namespace app\api\controller\cms;

class Position extends Base
{
    protected array $noNeedLogin = ['*'];
    protected $model = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\index\model\cms\ContentSort();
    }

    public function index()
    {
        // 获取参数
        $scode = $this->request->param('scode');
        if (!!$scode) {
            $data = $this->model->getPosition($scode);
            $this->success('获取成功', $data);
        } else {
            $this->error('必须传递当前分类scode参数');
        }
    }
}

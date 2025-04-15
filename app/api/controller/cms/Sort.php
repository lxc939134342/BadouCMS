<?php

namespace app\api\controller\cms;

class Sort extends Base
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
            $data = $this->model->getSort($scode);
            if (!$data) {
                $data = '';
            }
            $this->success('获取成功', $data);
        } else {
            $this->error('必须传递分类scode参数');
        }
    }
}

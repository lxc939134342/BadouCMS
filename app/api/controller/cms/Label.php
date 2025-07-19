<?php

namespace app\api\controller\cms;

class Label extends Base
{
    protected array $noNeedLogin = ['*'];
    protected $model = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\index\model\cms\Label();
    }

    public function index()
    {
        // 获取参数
        $name = $this->request->param('name');

        $data = $this->model->getLabelData();
        if ($name) {
            $data = $data[$name] ?? '';
        }
        $this->success('获取成功', $data);
    }
}

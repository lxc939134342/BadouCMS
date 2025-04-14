<?php

namespace app\api\controller\cms;

class Company extends Base
{
    protected array $noNeedLogin = ['*'];
    protected $model = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\index\model\cms\Company();
    }

    public function index()
    {
        // 获取参数
        $name = $this->request->param('name');

        $data = $this->model->getCompanyData();
        if ($name) {
            $data = $data[$name] ?? '';
        }
        $this->success('获取成功', $data);
    }
}

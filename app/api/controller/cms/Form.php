<?php

namespace app\api\controller\cms;

class Form extends Base
{
    protected array $noNeedLogin = ['*'];
    protected $model = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\index\model\cms\Form();
    }

    public function index()
    {
        // 获取参数
        $fcode = $this->request->param('fcode', 0);
        $num = $this->request->param('num', 1);
        $page = $this->request->param('page', 1);
        // 获取表单编码
        if (! $fcode) {
            $this->error('必须传递表单编码fcode');
        }

        // 读取数据
        $data = $this->model->getFormList($fcode, $num);
        if (isset($data['page'])) {
            unset($data['page']);
        }

        if (empty($data['data']) && $page) {
            $this->error('已经到底了！', $data);
        }
        $this->success('获取成功', $data);
    }
}

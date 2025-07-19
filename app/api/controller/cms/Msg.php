<?php

namespace app\api\controller\cms;

class Msg extends Base
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
        $num = $this->request->param('num', 10);
        $page = $this->request->param('page', 1);

        // 读取数据
        $data = $this->model->getFormList(1, $num);
        if (isset($data['page'])) {
            unset($data['page']);
        }

        if (empty($data['data']) && $page) {
            $this->error('已经到底了！', $data);
        }
        $this->success('获取成功', $data);
    }
}

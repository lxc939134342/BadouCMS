<?php

namespace app\api\controller\cms;

class Lists extends Base
{
    protected array $noNeedLogin = ['*'];
    protected $model = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\index\model\cms\Content();
    }

    public function index()
    {
        // 获取参数
        $acode = $this->request->param('acode', get_frontend_lang());
        $scode = $this->request->param('scode');
        $num = $this->request->param('num', 10);
        $order = $this->request->param('order', '');
        $page = $this->request->param('page', 1);

        // 读取数据
        $data = $this->model::contentList(['num' => $num,'acode' => $acode,'scode' => $scode,'order' => $order,'page' => 1]);
        if (isset($data['page'])) {
            unset($data['page']);
        }

        if (empty($data['data']) && $page) {
            $this->error('已经到底了！', $data);
        }
        $this->success('获取成功', $data);
    }
}

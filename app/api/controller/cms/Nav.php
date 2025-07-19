<?php

namespace app\api\controller\cms;

class Nav extends Base
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
        // 获取栏目树
        if (!$scode) {
            $data = $this->model->getSorts(get_frontend_lang());
        } else { // 获取子类
            $data = $this->model->getSortsSon(get_frontend_lang(), $scode);
        }

        $this->success('获取成功', $data);
    }
}

<?php

namespace app\api\controller\cms;

class Pics extends Base
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
        $id = $this->request->param('id', 0, 'int');
        $field = $this->request->param('field', 'pics');
        if (!!$id) {
            $pics = $this->model->getContentPics($id, $field, 0, true);
            $this->success('获取成功', $pics);
        } else {
            $this->error('必须传递内容id参数');
        }
    }
}

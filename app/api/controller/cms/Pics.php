<?php

namespace app\api\controller\cms;

class Pics extends Base
{
    protected $noNeedLogin = ['*'];
    protected $model = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\index\model\cms\Content();
    }

    public function index()
    {
        $id = $this->request->param('id', 0, 'int');
        $field = (string) $this->request->param('field', 'pics');
        if (!!$id) {
            if (!$this->model->isAllowedPicsField($field)) {
                $this->error('图片字段参数非法');
            }
            $pics = $this->model->getContentPics($id, $field, 0, true);
            $this->success('获取成功', $pics);
        } else {
            $this->error('必须传递内容id参数');
        }
    }
}

<?php

namespace app\api\controller\cms;

class Content extends Base
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
        $id = $this->request->param('id');
        if (!!$id) {
            // 读取数据
            $data = $this->model->getContent('', $id);
            if ($data) {
                $this->success('获取成功', $data);
            } else {
                $this->error('id为' . $id . '的内容已经不存在了！');
            }
        } else {
            $this->error('请求错误，传递的内容id有误！');
        }
    }
}

<?php

namespace app\api\controller\cms;

class About extends Base
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
        $scode = $this->request->param('scode');
        if (!!$scode) {
            // 读取数据
            $data = $this->model->getContent($scode);
            if ($data) {
                $this->success('获取成功', $data);
            } else {
                $this->error('分类编码为' . $scode . '的内容已经不存在了！');
            }
        } else {
            $this->error('请求错误，传递的内容scode有误！');
        }
    }
}

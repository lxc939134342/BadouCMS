<?php

namespace app\api\controller\cms;

class Search extends Base
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
        $keyword = $this->request->param('keyword', $this->request->param('title'));
        if (empty($keyword)) {
            $this->error(__('Please enter keywords'));
        }
        //禁止搜索过滤域名
        if (preg_match("/\.[a-z]{2,}/i", $keyword)) {
            $this->error(__('No Data'));
        }
        $keyword = strip_tags($keyword);
        $keyword = str_replace(strrchr($keyword, "."), "", $keyword);  //去掉带有后缀的关键词
        $keyword = mb_substr($keyword, 0, 15);
        $page = $this->request->param('page', 1);
        $fuzzy = $this->request->param('fuzzy', 1);

        $params = [
            'keyword' => $keyword,
            'fuzzy' => $fuzzy
        ];

        // 读取数据
        $data = $this->model::searchList($params);
        if (isset($data['page'])) {
            unset($data['page']);
        }

        if (empty($data['data']) && $page) {
            $this->error('已经到底了！', $data);
        }
        $this->success('获取成功', $data);
    }
}

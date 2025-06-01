<?php

namespace app\admin\controller\general;

use app\common\controller\Backend;
use app\common\model\Attachment as ModelAttachment;

class Attachment extends Backend
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new ModelAttachment();
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->isAjax()) {
            $mimetypeQuery = [];
            $filter = $this->request->request('filter');
            $filterArr = (array)json_decode($filter, true);
            if (isset($filterArr['category']) && $filterArr['category'] == 'unclassed') {
                $filterArr['category'] = ',unclassed';
                $this->request->get(['filter' => json_encode(array_diff_key($filterArr, ['category' => '']))]);
            }
            if (isset($filterArr['mimetype']) && preg_match("/(\/|\,|\*)/", $filterArr['mimetype'])) {
                $mimetype = $filterArr['mimetype'];
                $filterArr = array_diff_key($filterArr, ['mimetype' => '']);
                $mimetypeQuery = function ($query) use ($mimetype) {
                    $mimetypeArr = array_filter(explode(',', $mimetype));
                    foreach ($mimetypeArr as $index => $item) {
                        $query->whereOr('mimetype', 'like', '%' . str_replace("/*", "/", $item) . '%');
                    }
                };
            }
            $this->request->get(['filter' => json_encode($filterArr)]);

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->where($mimetypeQuery)
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            $cdnurl = preg_replace("/\/(\w+)\.php$/i", '', $this->request->root());
            foreach ($list as $k => &$v) {
                $v['fullurl'] = ($v['storage'] == 'local' ? $cdnurl : $this->view->config['upload']['cdnurl']) . $v['url'];
            }
            unset($v);
            $this->result('', $list->items(), $list->total());

        }
        return $this->view->fetch();
    }
}

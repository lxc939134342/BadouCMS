<?php

namespace app\admin\controller\cms;

class Single extends Base
{
    /**
     * 模型ID
     * @var string
     */
    protected int $mcode = 0;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Content();
        $this->mcode = $this->request->param('mcode') ?? 0;
        $this->assign('mcode', $this->mcode);
    }

    public function index()
    {
        if (!$this->request->isAjax()) {
            return $this->view->fetch();
        }

        if ($this->request->param('select')) {
            $this->select();
        }

        $contentsortModel = new \app\admin\model\cms\ContentSort();

        list($where, $sort, $order, $offset, $limit, $page, $alias, $bind) = $this->buildparams();
        foreach ($where as &$whereitem) {
            if ($whereitem[0] == 'content.scode') {
                $whereitem[1] = 'in';
                $whereitem[2] = $contentsortModel->getChildrenIds($whereitem[2], true, true);
            }

            // 因为date是时间格式 所以要进行处理
            if ($whereitem[0] == 'content.date') {
                foreach ($whereitem[2] as $key => $value) {
                    $whereitem[2][$key] = date('Y-m-d H:i:s', $value);
                }
            }
        }
        unset($whereitem);
        if ($this->mcode) {
            $where[] = [
                'contentsort.mcode',
                '=',
                $this->mcode
            ];
        }
        $where[] = [
            'content.acode','=',get_backend_lang()
        ];

        /* 查询子栏目数据 */
        $res = $this->model
            ->withJoin('contentsort')
            ->alias($alias)
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $this->result('', $res->items(), $res->total());
    }
}

<?php

namespace app\admin\controller\cms;

use Throwable;
use think\Exception;

class Single extends Base
{
    /**
     * 模型ID
     * @var string
     */
    protected $mcode = 0;

    /**
     * @var \app\admin\model\cms\Extfield
     */
    protected $extfieldModel;

    /**
    * Contentext模型对象
    * @var \app\admin\model\cms\ContentExt
    */
    protected $contentExtModel;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Content();
        $this->mcode = $this->request->param('mcode') ?? 0;
        $this->contentExtModel = new \app\admin\model\cms\ContentExt();
        $this->extfieldModel = new \app\admin\model\cms\Extfield();
        $this->assign('mcode', $this->mcode);
        $this->view->assign('custom_fields', $this->extfieldModel->getModelFields($this->mcode));
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

    public function edit()
    {
        $ids = $this->request->param('ids/d');
        $row = $this->model->find($ids);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        /* 获取扩展数据 */
        $extRow = $this->contentExtModel->where('contentid', $row['id'])->find();
        if ($extRow) {
            /* 合并数据 */
            $extRowArr = $extRow->toArray();
            $extRowArr = $this->contentExtModel->formatValue($this->mcode, $extRowArr);
            $row->appendData($extRowArr);
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->getPostData('row/a');
            $data['filename'] ?? $data['filename'] = $row['filename'];
            $data['description'] ?? $data['description'] = $row['description'];
            $data['ico'] ?? $data['ico'] = $row['ico'];
            $data['scode'] ?? $data['scode'] = $row['scode'];
            $data['title'] ?? $data['title'] = $row['title'];
            $noFilterData = $this->request->post('row/a', '', 'trim');
            $data['content'] = isset($noFilterData['content']) ? xss_clean($noFilterData['content']) : '';
            $data['update_user'] = $this->auth->username;
            $result = false;
            $this->model->startTrans();
            try {
                $data['id'] = $row['id'];
                $this->modelValidateFunction($data);

                if ($data['filename'] && ! preg_match('/^[a-zA-Z0-9\-_\/]+$/', $data['filename'])) {
                    throw new Exception(__('URL name only allows letters, numbers, lines, underscores'));
                }

                // 自动提起前一百个字符为描述
                if (! $data['description'] && isset($data['content'])) {
                    $data['description'] = escape_string(clear_html_blank(substr_both(strip_tags($data['content']), 0, 150)));
                }

                // 无缩略图时，自动提取文章第一张图为缩略图
                if (! $data['ico'] && preg_match('/<img\s+.*?src=\s?[\'|\"](.*?(\.gif|\.jpg|\.png|\.jpeg))[\'|\"].*?[\/]?>/i', decode_string($data['content']), $srcs) && isset($srcs[1])) {
                    $data['ico'] = $srcs[1];
                }

                // 检查自定义URL名称
                if ($data['filename']) {
                    while ($this->model->checkFilename($data['filename'])) {
                        $data['filename'] = $data['filename'] . '-' . mt_rand(1, 20);
                    }
                }

                $result = $row->save($data);
                /* 添加扩展数据 */
                $extdata = $this->contentExtModel->getExtData($noFilterData, $this->mcode);
                $extdata['contentid'] = $row['id'];

                if ($extRow) {
                    $extRow->save($extdata);
                } else {
                    $this->contentExtModel->save($extdata);
                }

                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }

        $this->view->assign('custom_fields', $this->extfieldModel->getModelFields($this->mcode));
        $this->assign('row', $row);
        return $this->view->fetch();
    }
}

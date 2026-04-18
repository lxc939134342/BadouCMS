<?php

namespace app\admin\controller\cms;

use Throwable;
use think\Exception;
use badou\EventContext;

class Single extends Base
{
    /**
     * 模型ID
     * @var string
     */
    public int $mcode = 0;

    /**
     * @var \app\admin\model\cms\Extfield
     */
    protected $extfieldModel;

    /**
     * Contentext模型对象
     * @var \app\admin\model\cms\ContentExt
     */
    protected $contentExtModel;

    protected $noNeedRight = ['getFieldHtml'];

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
            $this->assignHook('index');
            return $this->view->fetch();
        }

        if ($this->request->param('select')) {
            $this->select();
        }

        $contentsortModel = new \app\admin\model\cms\ContentSort();
        $modelsModel = new \app\admin\model\cms\Models();
        $mcodes = $modelsModel->getMcodesOfType(1);

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
        $where[] = [
            'contentsort.mcode',
            'in',
            $mcodes
        ];
        $where[] = [
            'content.acode',
            '=',
            get_backend_lang(),
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
            $row->setAttrs($extRowArr);
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
                // 触发观察者 - 修改前
                $context = new EventContext($data, ['row' => $row]);
                $this->triggerObserver('BeforeEdit', $context, $this);
                if ($context->isIntercepted()) {
                    $this->error($context->getMessage());
                }
                $data = $context->getData();

                $this->modelValidateFunction($data);

                // 检查自定义URL名称
                if ($data['filename']) {
                    $data['filename'] = $this->model->checkFilename($data['filename'], [['id', '<>', $row['id']]]);
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

                // 触发观察者 - 修改后
                $this->triggerObserver('AfterEdit', $row, $data, $this);
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

        $this->assign('row', $row);
        $this->assignHook('edit', ['main_top', 'main_mid', 'main_bottom', 'side_top', 'side_bottom', 'footer', 'scripts'], $row->toArray());
        return $this->view->fetch();
    }

    public function getFieldHtml()
    {
        $id = $this->request->param('id');
        $acode = $this->request->param('content_acode', get_backend_lang());
        $mcode = $this->request->param('mcode');
        $scode = $this->request->param('scode');
        if (!$mcode && !$scode) {
            $this->error(__('Invalid parameters'));
        }
        if (!$mcode) {
            $contentSortModel = new \app\admin\model\cms\ContentSort();
            $mcode = $contentSortModel::where('scode', $scode)->value('mcode');
        }
        if ($id) {
            $rowitem = $this->model->find($id);
            /* 获取扩展数据 */
            $extRow = $this->contentExtModel->where('contentid', $rowitem['id'])->find();
            if ($extRow) {
                /* 合并数据 */
                $extRowArr = $extRow->toArray();
                $extRowArr = $this->contentExtModel->formatValue($mcode, $extRowArr);
                $rowitem->setAttrs($extRowArr);
            }
        }

        $custom_fields = [];
        $custom_fields = $this->extfieldModel->getModelFields($mcode);
        if ($custom_fields) {
            foreach ($custom_fields as $key => $field) {
                $custom_fields[$key]['form_name'] = 'row[' . $field['name'] . ']';
                $custom_fields[$key]['form_id'] = $field['name'];
                $custom_fields[$key]['form_value'] = $rowitem[$field['name']] ??  '';
                $custom_fields[$key]['form_acode'] = $acode;
            }
        }
        $this->view->assign('custom_fields', $custom_fields);
        $this->view->assign('row', $rowitem);

        // p($custom_fields);

        $this->success('', null, ['html' => $this->view->fetch('cms/common/builder/fields')]);
    }
}

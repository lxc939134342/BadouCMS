<?php

// +----------------------------------------------------------------------
// | BADOUCMS [ 八斗网站系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2024-2030 http://doc.ldcode.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: lande <939134342@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\controller\cms;

use app\admin\model\cms\Models;
use Throwable;
use badou\Tree;
use think\facade\Db;
use app\admin\model\UserLevel;
use badou\Filesystem;
use badou\EventContext;


/**
 * CMS文章内容
 */
class Content extends Base
{
    protected $noNeedRight = ['getFieldHtml', 'getContentSort'];
    /**
     * @var \app\admin\model\cms\Content
     */
    protected $model;
    /**
     * Contentext模型对象
     * @var \app\admin\model\cms\ContentExt
     */
    protected $contentExtModel;

    /**
     * @var \app\admin\model\cms\Extfield
     */
    protected $extfieldModel;

    protected $weighField = 'sorting';

    protected $quickSearchField = ['id', 'title'];
    // 开启关联查询
    protected $relationSearch = true;

    protected $modelValidate = true;

    protected $multiFields = 'status,sorting,istop,isrecommend,isheadline';

    /**
     * 模型ID
     * @var string
     */
    public int $mcode = 0;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Content();
        $this->contentExtModel = new \app\admin\model\cms\ContentExt();
        $this->extfieldModel = new \app\admin\model\cms\Extfield();
        $levelModel = new UserLevel();
        $this->mcode = $this->request->param('mcode') ?? 0;
        $this->assign('mcode', $this->mcode);
        $this->assign('levellist', $levelModel->getLevelList());
        $this->assign('gtypelist', $levelModel->getGtypeList());
        $this->assign('tpls', $this->getTpls());
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
        $mcodes = $modelsModel->getMcodesOfType(2);

        list($where, $sort, $order, $offset, $limit, $page, $alias, $bind) = $this->buildparams();
        foreach ($where as &$whereitem) {
            if ($whereitem[0] == 'content.scode') {
                $whereitem[1] = 'in';
                $whereitem[2] = $contentsortModel->getChildrenIds($whereitem[2], true, true);
            }

            // 因为date是时间格式 所以要进行处理
            if ($whereitem[0] == 'content.date' && is_array($whereitem[2])) {
                foreach ($whereitem[2] as $key => $value) {
                    // 如果不是数字（即是日期字符串），则尝试转换为时间戳再格式化，或者如果是合法的日期字符串则直接使用
                    $whereitem[2][$key] = is_numeric($value) ? date('Y-m-d H:i:s', (int)$value) : $value;
                }
            }
        }
        unset($whereitem);
        $where[] = ['contentsort.mcode', 'in', $mcodes];
        $where[] = ['content.acode', '=', get_backend_lang()];

        /* 查询子栏目数据 */
        $res = $this->model
            ->alias($alias)
            ->withJoin(['contentsort'])
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);

        $res->each(function ($item, $key) {
            $item->view_url = $this->getViewUrl($item);
        });

        $this->result('', $res->items(), $res->total());
    }

    protected function getViewUrl($data)
    {
        if (!isset($data['contentsort']['filename'])) {
            return '';
        }

        // 增加 outlink 前缀处理
        if (isset($data['outlink']) && $data['outlink']) {
            $outlink = $data['outlink'];
            // 如果不是以 http:// 或 https:// 开头，则添加前导 /
            if (!preg_match('#^https?://#i', $outlink)) {
                $outlink = '/' . ltrim($outlink, '/');
            }
            return $outlink;
        }

        $url = (string) bdurl($data['contentsort']['type'], $data['contentsort']['urlname'], 'content', $data['scode'], $data['contentsort']['filename'], $data['id'], $data['filename']);

        $url = preg_replace("/\/((?!index)[\w]+)\.php\//i", "/", $url);
        return $url;
    }

    /**
     * 添加
     */
    public function add()
    {
        if (!$this->isAjax()) {
            $this->assignHook('add', ['main_top', 'main_mid', 'main_bottom', 'side_top', 'side_bottom', 'footer', 'scripts'], []);
            return $this->view->fetch();
        }
        $data = $this->getPostData('row/a');
        $noFilterData = $this->request->post('row/a', '', 'trim');
        $data['content'] = isset($noFilterData['content']) ? xss_clean($noFilterData['content']) : '';

        // 构建数据
        $default = [
            'acode' => get_backend_lang(),
            'scode' => '',
            'subscode' => '',
            'title' => '',
            'titlecolor' => '',
            'subtitle' => '',
            'filename' => '',
            'author' => $this->auth->nickname,
            'source' => '',
            'outlink' => '',
            'date' => date('Y-m-d H:i:s'),
            'ico' => '',
            'pics' => '',
            'picstitle' => '',
            'content' => '',
            'tags' => '',
            'enclosure' => '',
            'keywords' => '',
            'description' => '',
            'sorting' => 255,
            'status' => 1,
            'istop' => 0,
            'isrecommend' => 0,
            'isheadline' => 0,
            'gid' => '',
            'gtype' => '',
            'gnote' => '',
            'visits' => 0,
            'likes' => 0,
            'oppose' => 0,
            'custom_tpl' => '',
            'create_user' => $this->auth->username,
            'update_user' => $this->auth->username,
        ];

        $data = array_merge($default, $data);
        $data['sorting'] = $data['sorting'] ?? 255;
        $result = false;
        Db::startTrans();
        try {
            // 触发观察者 - 添加前
            $context = new EventContext($data);
            $this->triggerObserver('BeforeAdd', $context, $this);
            if ($context->isIntercepted()) {
                $this->error($context->getMessage());
            }
            $data = $context->getData();

            // 模型验证
            $this->modelValidateFunction($data);

            // 检查自定义URL名称
            if ($data['filename']) {
                $data['filename'] = $this->model->checkFilename($data['filename']);
            }
            $data['aucode'] = $this->model->getUniqueAucode();
            $result = $this->model->save($data);

            /* 添加扩展数据 */
            $extdata = $this->contentExtModel->getExtData($noFilterData, $this->mcode);
            $extdata['contentid'] = $this->model->id;

            $this->contentExtModel->save($extdata);

            // 触发观察者 - 添加后
            $this->triggerObserver('AfterAdd', $data, $this);
            Db::commit();
        } catch (Throwable $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result !== false) {
            $this->success(__('Added successful'));
        } else {
            $this->error(__('No rows were added'));
        }
    }

    public function del()
    {
        $where             = [];
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$this->dataLimitField, 'in', $dataLimitAdminIds];
        }

        $ids     = $this->request->param('ids');
        $all     = $this->request->param('all'); // 保留参数接收，供钩子逻辑使用

        // 触发观察者 - 删除前
        $res = $this->triggerObserver('BeforeDel', $ids, $this);
        if (is_array($res)) {
            $ids = $res;
        }

        $where[] = [$this->pk, 'in', $ids];
        $data    = $this->model->where($where)->select();

        $count = 0;
        $this->model->startTrans();
        try {
            if ($data) {
                foreach ($data as $v) {
                    $count += $v->delete();
                }
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }

        if ($count) {
            $this->success(__('Delete successful'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }

    /**
     * 修改
     */
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
            $data['sorting'] = $data['sorting'] ?? $row['sorting'];
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

    /**
     * 复制数据
     */
    public function copy()
    {
        $ids = $this->request->param('ids');
        $scode = $this->request->param('scode');
        if (!$this->isAjax()) {
            return $this->view->fetch();
        }
        if (!$ids) {
            $this->error(__('Please select the data'));
        }
        if (! $scode) {
            $this->error(__('Please Select a Category'));
        }
        $res = $this->model->copyContent($ids, $scode);
        if (!$res) {
            $this->error(__('Copy failed'));
        }
        $this->success(__('Copy successful'));
    }

    /**
     * 移动数据
     */
    public function move()
    {
        $ids = $this->request->param('ids');
        $scode = $this->request->param('scode');
        if (!$this->isAjax()) {
            return $this->view->fetch();
        }

        if (!$ids) {
            $this->error(__('Please select the data'));
        }
        if (! $scode) {
            $this->error(__('Please Select a Category'));
        }
        $res = $this->model->moveContent($ids, $scode);
        if (!$res) {
            $this->error(__('Move failed'));
        }
        $this->success(__('Move successful'));
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

    /**
     * 选择内容
     */
    public function selectpage()
    {
        $custom = (array)$this->request->request("custom/a");
        $where = [
            'acode' => $custom['acode'],
        ];

        if (isset($custom['mcode']) && $custom['mcode']) {
            // 获取属于指定模型的所有栏目代码
            $contentsortModel = new \app\admin\model\cms\ContentSort();
            $scodeList = $contentsortModel->where('mcode', $custom['mcode'])->column('scode');

            if (!empty($scodeList)) {
                $where[] = ['scode', 'in', $scodeList];
            }
        }

        $res = $this->model
            ->where($where)
            ->order('id', 'desc')
            ->select();

        $this->success('ok', '', ['list' => $res, 'total' => $res->count()]);
    }

    public function getContentSort()
    {
        $contentSortModel = new \app\admin\model\cms\ContentSort();
        $modelsModel = new Models();
        $tree = Tree::instance(['childname' => 'children']);
        $acode = $this->request->param('acode', get_backend_lang());
        $mcode = $this->request->param('mcode');
        $scode = $this->request->param('scode');
        if ($scode && !$mcode) {
            $mcode = $contentSortModel->where('scode', $scode)->value('mcode');
        }
        if (!$mcode) {
            $mcode = $modelsModel->getMcodesOfType(2);
        }
        $where[] = [
            'acode',
            '=',
            $acode
        ];
        $where[] = [
            'mcode',
            'in',
            $mcode
        ];

        $res = $contentSortModel
            ->where($where)
            ->order('sorting asc,id desc')
            ->select();
        $res->each(function ($item) {
            $item->spread = true;
        });

        /**
         * 树状表格必看注释一
         * 1. 获取表格数据（没有分页，所以简化了以上的数据查询代码）
         * 2. 递归的根据指定字段组装 children 数组，此时直接给前端，表格就可以正常的渲染为树状了，一个方法搞定
         */
        $list = $tree->init($res->toArray(), 'pcode', null, 'scode')->multipleChild();

        $this->success('ok', '', ['list' => $list, 'total' => $res->count()]);
    }

    /**
     * 获取模版文件列表
     */
    protected function getTpls()
    {
        $acode = get_backend_lang();
        $template = Db::name('cms_site')->where('acode', $acode)->value('theme');
        if ($template == '') {
            $template = 'default';
            Db::name('cms_site')->where('acode', $acode)->update(['theme' => 'default']);
        }

        $path = root_path() . 'template' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR . $template . DIRECTORY_SEPARATOR;
        $list = [];
        if (is_dir($path)) {
            $files = Filesystem::getDirFiles($path, ['html']);
            $list = [];
            foreach ($files as $key => $value) {
                $list[] = ['id' => $key, 'name' => $value];
            }
        }

        return $list;
    }
}

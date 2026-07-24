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

use app\admin\model\UserLevel;
use Throwable;
use badou\Tree;
use think\facade\Db;
use badou\Filesystem;
use app\admin\model\cms\Models;
use badou\EventContext;

/**
 * 栏目管理
 */
class ContentSort extends Base
{
    /**
     * ContentSort模型对象
     * @var \app\admin\model\cms\ContentSort
     * @phpstan-var \app\admin\model\cms\ContentSort
     */
    protected $model;
    protected $withJoinTable = ['models'];
    protected $pk = 'scode';
    protected $modelValidate = true;
    protected $multiFields = 'status,sorting';
    /**
     *
     * @var \badou\Tree
     */
    protected object $tree;
    public function initialize(): void
    {
        parent::initialize();
        $this->tree  = Tree::instance();
        $this->model = new \app\admin\model\cms\ContentSort();
        $modelsModel = new Models();
        $levelModel = new UserLevel();

        $res = $modelsModel->where('status', 1)
            ->order('id', 'desc')
            ->select();

        $this->assign('models', $res);
        $this->assign('levellist', $levelModel->getLevelList());
        $this->assign('gtypelist', $levelModel->getGtypeList());
        $this->assign('tpls', $this->getTpls());
        $this->assignconfig('models', $res);
    }

    public function index()
    {
        if (!$this->isAjax()) {
            $this->assignHook('index');
            return $this->view->fetch();
        }

        list($where, $sort, $order, $offset, $limit, $page, $alias, $bind) = $this->buildparams();
        $istop = $this->request->param('istop/d', 1);

        $where[] = [
            'acode',
            '=',
            get_backend_lang()
        ];

        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($sort, $order)
            ->select()->toArray();

        /**
         * 树状表格必看注释一
         * 1. 获取表格数据（没有分页，所以简化了以上的数据查询代码）
         * 2. 递归的根据指定字段组装 children 数组，此时直接给前端，表格就可以正常的渲染为树状了，一个方法搞定
         */
        $list = $this->tree->init($res, 'pcode', null, 'scode')->multipleChild();

        $this->result('', $list);
    }

    /**
     * 删除
     * @throws Throwable
     */
    public function del()
    {
        $where             = [];
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$this->dataLimitField, 'in', $dataLimitAdminIds];
        }

        $ids = $this->request->param('ids');

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
            foreach ($data as $v) {
                $count += $v->delete();
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

    /**
     * 添加
     */
    public function add()
    {
        if (!$this->isAjax()) {
            $this->assignHook('add', ['main_top', 'main_mid', 'main_bottom', 'side_top', 'side_bottom', 'footer', 'scripts'], []);
            return $this->view->fetch();
        }

        if ($this->request->isPost()) {
            $data = $this->getPostData('row/a');
            $result = false;
            $this->model->startTrans();
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
                $lastcode = $this->model->getLastCode();
                $scode = get_auto_code($lastcode);
                $default = [
                    'acode'       => get_backend_lang(),
                    'pcode'       => 0,
                    'scode'       => $scode,
                    'name'        => '',
                    'mcode'       => 0,
                    'listtpl'     => '',
                    'contenttpl'  => '',
                    'status'      => 1,
                    'gid'         => 0,
                    'gtype'       => 4,
                    'subname'     => '',
                    'filename'    => '',
                    'outlink'     => '',
                    'ico'         => '',
                    'pic'         => '',
                    'title'       => '',
                    'keywords'    => '',
                    'description' => '',
                    'sorting'     => '255',
                    'create_user' => $this->auth->username,
                    'update_user' => $this->auth->username,
                    'def1' => '',
                    'def2' => '',
                    'def3' => '',
                ];

                $data = array_merge($default, $data);

                $result = $this->model->save($data);
                if ($result !== false) {
                    $this->triggerObserver('AfterAdd', $data, $this);
                }
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Add successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }
        $this->assignHook('add', ['main_top', 'main_mid', 'main_bottom', 'side_top', 'side_bottom', 'footer', 'scripts'], []);
        return $this->view->fetch();
    }

    /**
     * 编辑
     * @throws Throwable
     */
    public function edit()
    {
        $id  = $this->request->param('ids');
        $row = $this->model->where($this->pk, 'in', $id)->find();
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->getPostData('row/a');
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data   = $this->preExcludeFields($data);
            $data['listtpl']    = $data['listtpl'] ?? '';
            $data['contenttpl'] = $data['contenttpl'] ?? '';
            $data['pcode'] = isset($data['pcode']) ? (int)$data['pcode'] : 0;
            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $this->modelValidateFunction($data);
                }

                // 触发观察者 - 修改前
                $context = new EventContext($data, ['row' => $row]);
                $this->triggerObserver('BeforeEdit', $context, $this);
                if ($context->isIntercepted()) {
                    $this->error($context->getMessage());
                }
                $data = $context->getData();

                $result = $row->save($data);
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
        $this->view->assign('row', $row);
        $this->assignHook('edit', ['main_top', 'main_mid', 'main_bottom', 'side_top', 'side_bottom', 'footer', 'scripts'], $row->toArray());
        return $this->view->fetch();
    }

    /**
     * 批量添加
     */
    public function batchAdd()
    {
        if ($this->request->isPost()) {
            $data = $this->getPostData('row/a');
            $result = false;
            $this->model->startTrans();
            try {
                // 触发观察者 - 添加前
                $context = new EventContext($data);
                $this->triggerObserver('BeforeBatchAdd', $context, $this);
                if ($context->isIntercepted()) {
                    $this->error($context->getMessage());
                }
                $data = $context->getData();

                // 模型验证
                $this->modelValidateFunction($data);
                $multiplename = str_replace('，', ',', $data['name']);
                $names = explode(',', $multiplename);
                $listtpl    = isset($data['listtpl']) ? $data['listtpl'] : '';
                $contenttpl = isset($data['contenttpl']) ? $data['contenttpl'] : '';

                $lastcode = $this->model->getLastCode();
                $scode = get_auto_code($lastcode);
                $datalist = [];
                foreach ($names as $key => $value) {
                    $datalist[] = array(
                        'acode'      => get_backend_lang(),
                        'pcode'      => $data['pcode'] ?? 0,
                        'scode'      => $scode,
                        'name'       => $value,
                        'mcode'      => $data['mcode'],
                        'listtpl'    => $listtpl,
                        'contenttpl' => $contenttpl,
                        'status' => $data['status'],
                        'gid' => 0,
                        'gtype' => 4,
                        'subname' => '',
                        'filename' => '',
                        'outlink' => '',
                        'ico' => '',
                        'pic' => '',
                        'title' => '',
                        'keywords' => '',
                        'description' => '',
                        'sorting' => '255',
                        'create_user' => '',
                        'update_user' => '',
                        'def1' => '',
                        'def2' => '',
                        'def3' => '',
                    );
                    $scode = get_auto_code($scode);
                }
                $result = $this->model->saveAll($datalist);
                $this->triggerObserver('AfterBatchAdd', $datalist, $this);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Add successful'));
            } else {
                $this->error(__('No rows were added'));
            }
        }
        $this->assignHook('batchadd', ['main_top',  'main_bottom',  'footer', 'scripts'], []);
        return $this->view->fetch();
    }


    public function selectpage()
    {
        $custom = (array)$this->request->request("custom/a");
        $where = [
            'acode' => $custom['acode'],
        ];
        if (isset($custom['mcode'])) {
            $where['mcode'] = $custom['mcode'];
        }
        $res = $this->model
            ->where($where)
            ->order('scode', 'desc')
            ->select();

        $list = $this->tree->init($res->toArray(), 'pcode', null, 'scode', 'children')->multipleChild();
        $top = ['scode' => 0, 'name' => '顶级栏目'];
        array_unshift($list, $top);
        $this->success('', '', ['list' => $list, 'total' => $res->count() + 1]);
    }
}

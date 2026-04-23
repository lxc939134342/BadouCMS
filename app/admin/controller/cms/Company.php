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

use Throwable;
use badou\TableManager;
use think\facade\Cache;
use badou\EventContext;

/**
 * 公司信息
 */
class Company extends Base
{
    /**
     * Company模型对象
     * @var \app\admin\model\cms\Company
     */
    protected $model;
    protected $pk = 'acode';

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Company();
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */

    public function index()
    {
        $config = $this->model->where('acode', get_backend_lang())->findOrEmpty();

        if ($config->isEmpty()) {
            $columns = TableManager::getTableColumns('cms_company', false, 'mysql');
            $config->setAttrs(array_fill_keys(array_keys($columns), ''));
        }
        $this->assign('row', $config);
        $this->assignHook('index', ['main_top', 'main_mid', 'main_bottom', 'side_top', 'side_bottom', 'footer', 'scripts'], $config->toArray());
        return $this->view->fetch();
    }

    /**
     * 添加数据
     * @return void
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->getPostData('row/a');
            $data['acode'] = get_backend_lang();
            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                $this->modelValidateFunction($data);

                // 触发观察者 - 添加前
                $context = new EventContext($data);
                $this->triggerObserver('BeforeAdd', $context, $this);
                if ($context->isIntercepted()) {
                    $this->error($context->getMessage());
                }
                $data = $context->getData();

                $result = $this->model->save($data);

                // 触发观察者 - 添加后
                $this->triggerObserver('AfterAdd', $data, $this);

                $this->model->commit();
                Cache::tag('cms_cache')->clear();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows were added'));
            }
        }
    }

    /**
     * 保存数据
     * @return void
     */
    public function edit(): void
    {
        if ($this->request->isPost()) {
            $acode = get_backend_lang();
            $row = $this->model->where('acode', $acode)->find();
            if (!$row) {
                $this->add();
            } else {
                $data = $this->getPostData('row/a');
                $result = false;
                $this->model->startTrans();
                try {
                    // 模型验证
                    $this->modelValidateFunction($data);

                    // 触发观察者 - 修改前
                    $context = new EventContext($data, ['row' => $row]);
                    $this->triggerObserver('BeforeEdit', $context, $this);
                    if ($context->isIntercepted()) {
                        $this->error($context->getMessage());
                    }
                    $data = $context->getData();

                    $result = $row->save($data);

                    // 触发观察者 - 修改后
                    $this->triggerObserver('AfterEdit', $row, $data, $this);

                    $this->model->commit();
                    Cache::tag('cms_cache')->clear();
                } catch (Throwable $e) {
                    $this->model->rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success(__('Update successful'));
                } else {
                    $this->error(__('No rows were added'));
                }
            }
        }
    }
}

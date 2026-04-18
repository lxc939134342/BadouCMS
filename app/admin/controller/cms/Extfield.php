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

/**
 * 模型字段
 */
class Extfield extends Base
{
    /**
     * Extfield模型对象
     * @var \app\admin\model\cms\Extfield
     * @phpstan-var \app\admin\model\cms\Extfield
     */
    protected $model;

    protected string $weighField = 'sorting';

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Extfield();
        $models = new Models();
        $list = $models->column('name', 'mcode');
        $typeText = $this->model->typeListTextMap();
        $this->view->assign('models', $list);
        $this->view->assign('typeText', $typeText);
        $this->assignconfig('models', $list);
        $this->assignconfig('typeText', $typeText);
    }

    /**
     * 添加
     */
    public function add()
    {
        if (!$this->isAjax()) {
            $this->assignHook('add', ['fields', 'scripts']);
            return $this->view->fetch();
        }
        $data = $this->request->post('row/a');
        if (!$data) {
            $this->error(__('Parameter %s can not be empty', ['']));
        }

        $data = $this->preExcludeFields($data);
        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $data[$this->dataLimitField] = $this->auth->id;
        }

        $result = false;
        $this->model->startTrans();
        try {
            // 模型验证
            if ($this->modelValidate) {
                $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                if (class_exists($validate)) {
                    $validate = new $validate();
                    if ($this->modelSceneValidate) {
                        $validate->scene('add');
                    }
                    $validate->failException(true)->check($data);
                }
            }
            $result = $this->model->save($data);
            $this->model->commit();
        } catch (\Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success(__('Add successful'));
    }

    /**
     * 编辑
     * @throws \Throwable
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
            $data = $this->request->post('row/a');
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data   = $this->preExcludeFields($data);
            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) {
                            $validate->scene('edit');
                        }

                        $validate->failException(true)->check($data);
                    }
                }
                $result = $row->save($data);
                $this->model->commit();
            } catch (\Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }

        $this->assignHook('edit', ['fields', 'scripts'], $row->toArray());
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }
}

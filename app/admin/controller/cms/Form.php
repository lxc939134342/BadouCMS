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

/**
 * 自定义表单
 */
class Form extends Base
{
    /**
     * Form模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\Form
     */
    protected $model;

    /**
     * FormField模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\FormField
     */
    protected $formFieldModel;

    protected $noNeedRight = ['resetprefix'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Form();
        $this->formFieldModel = new \app\admin\model\cms\FormField();
    }

    public function add()
    {
        if (!$this->isAjax()) {
            return $this->view->fetch();
        }
        $data = $this->request->post('row/a');
        $data['create_user'] = $this->auth->nickname;
        $data['update_user'] = $this->auth->nickname;
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
                    $validate->check($data);
                }
            }
            $result = $this->model->save($data);
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success(__('Add successful'));
    }

    /* 重置表前缀 */
    public function resetprefix()
    {
        $ids = $this->request->post('ids/a');
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', ['ids']));
        }

        // 查找对应id的表名称
        $forms = $this->model->where('id', 'in', $ids)->select();
        if ($forms->isEmpty()) {
            $this->error(__('No data found'));
        }

        $this->model->startTrans();
        try {
            foreach ($forms as $v) {
                $tableName = $v['table_name'];
                // 使用正则表达式去掉cms之前的部分，保留cms及之后的部分
                $newTableName = preg_replace('/^.*?(?=cms)/', '', $tableName);
                // 如果前缀是ay_ 就修改成 cms_
                if (strpos($newTableName, 'ay_') === 0) {
                    $newTableName = preg_replace('/^ay_/', 'cms_', $newTableName);
                }
                $v->table_name = $newTableName;
                $v->save();
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }

        $this->success(__('Update successful'));
    }
}

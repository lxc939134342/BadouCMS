<?php

namespace app\admin\controller\cms;

use Throwable;
use think\Exception;

class Models extends Base
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Models();
        $typeList = $this->model->getTypeList();
        $this->assign("type_list", $typeList);
        $this->assignconfig("type_list", $this->model->getTypeList());
    }

    /**
     * 添加
     * @return void
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->getPostData();
            // 构建数据
            $default = array(
                'mcode' => 0,
                'name' => '',
                'type' => '',
                'urlname' => '',
                'listtpl' => '',
                'contenttpl' => '',
                'status' => 1,
                'issystem' => 0,
                'create_user' => $this->auth->username,
                'update_user' => $this->auth->username
            );

            $data = array_merge($default, $data);
            $result = false;
            $this->model->startTrans();
            try {
                $data['mcode'] = get_auto_code($this->model->getLastCode());
                // 模型验证
                $this->modelValidateFunction($data);
                $result = $this->model->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }
    }

    public function del($ids = null): void
    {
        if (!$this->request->isDelete() || !$ids) {
            $this->error(__('Parameter error'));
        }

        $where             = [];
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$this->dataLimitField, 'in', $dataLimitAdminIds];
        }

        $pk      = $this->model->getPk();
        $where[] = [$pk, 'in', $ids];

        $count = 0;
        $data  = $this->model->where($where)->select();
        $this->model->startTrans();
        try {
            foreach ($data as $v) {
                if ($v['issystem'] == 1) {
                    throw new Exception('系统模型不允许删除');
                }
                $count += $v->delete();
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success(__('Deleted successfully'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }
}

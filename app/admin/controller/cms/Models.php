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
use Exception;

/**
 * 模型管理
 */
class Models extends Base
{
    /**
     * Models模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\Models
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected string|array $quickSearchField = ['id','name'];

    /**
     * 当designType为以下值时:
     * 1. 出入库字符串到数组转换
     * 2. 默认值转数组
     * @var array
     */
    protected array $dtStringToArray = ['checkbox', 'selects', 'remoteSelects', 'city', 'images', 'files'];


    protected array $noNeedLogin = [''];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Models();
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

        $data = [
            'typeList' => $this->model->getTypeList()
        ];
        $this->success('ok', $data);
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

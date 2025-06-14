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
 * 区域管理
 */
class Area extends Base
{
    /**
     * Area模型对象
     * @var \app\admin\model\cms\Area
     */
    protected $model;

    protected string|array $quickSearchField = ['id'];

    /**
     * 默认排序
     * @var string|array
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Area();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->getPostData('row/a');

            // 构建数据
            $default = [
                'acode' => get_backend_lang(),
                'pcode' => 0,
                'name' => '',
                'domain' => '',
                'is_default' => 0,
                'create_user' => $this->auth->username,
                'update_user' => $this->auth->username
            ];
            $data = array_merge($default, $data);
            $result = false;
            $this->model->startTrans();
            try {
                $this->modelValidateFunction($data);
                $result = $this->model->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successful'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        return $this->fetch();
    }
}

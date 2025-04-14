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
     * @var object
     * @phpstan-var \app\admin\model\cms\Area
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected string|array $quickSearchField = ['id'];

    /**
     * 默认排序
     * @var string|array
     */
    protected string|array $defaultSortField = ['is_default' => 'desc','id' => 'asc'];

    protected array $noNeedPermission = ['get_langs'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Area();
    }

    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->getPostData();

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
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 获取语言列表
     * @return void
     */
    public function get_langs(): void
    {
        $langs = $this->model->order($this->defaultSortField)->column('id,acode as value,name as label');

        $this->success('ok', $langs);
    }
}

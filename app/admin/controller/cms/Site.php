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

/**
 * 站点配置
 */
class Site extends Base
{
    /**
     * Site模型对象
     * @var  \app\admin\model\cms\Site
     */
    protected $model;

    protected array|string $preExcludeFields = ['id'];


    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Site();
    }

    /**
    * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
    */
    public function index()
    {
        $config      = $this->model->where('acode', get_backend_lang())->find();

        if (!$config) {
            $columns = TableManager::getTableColumns('cms_site', false, 'mysql');
            foreach ($columns as $key => $value) {
                $config[$key] = '';
            }
        }
        $this->assign('row', $config);
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
                $result = $this->model->save($data);
                $this->model->commit();
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
                    $result = $row->save($data);
                    $this->model->commit();
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

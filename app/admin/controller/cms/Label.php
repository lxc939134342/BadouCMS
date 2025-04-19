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

use think\facade\Cache;
use Throwable;

/**
 * 定制标签
 */
class Label extends Base
{
    /**
     * Label模型对象
     * @var  \app\admin\model\cms\Label
     * @phpstan-var \app\admin\model\cms\Label
     */
    protected object $model;

    protected array|string $preExcludeFields = ['create_time', 'update_time'];

    protected string|array $quickSearchField = ['id'];
    protected array $noNeedPermission = ['typeText'];

    protected bool $modelValidate = true;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Label();

        $this->rules = [
            'name|'.__('name') => 'require|unique:cms_label',
            'type|'.__('type') => 'require',
            'description|'.__('description') => 'require'
        ];
    }

    public function content(): void
    {
        if ($this->request->isPost()) {
            $data = $this->getPostData();
            // 处理POST数据
            // 如果值是数组则转换为逗号分隔的字符串
            // 如果是文本则将换行符替换为<br>标签
            foreach ($data as $key => $value) {
                if (is_array($value['value'])) {
                    $data[$key]['value'] = implode(',', $value['value']);
                } else {
                    /* 兼容 windows与linux */
                    $data[$key]['value'] = str_replace(["\r\n", "\n"], '<br>', $value['value']);
                }
            }
            $this->model->saveAll($data);
            Cache::delete('cms_label');
            $this->success(__('Update successful'));
        } else {
            $list = $this->model->field('id,name,value,description,type')->select()->toArray();
            $typeListComponentMap = $this->model->typeListComponentMap();
            foreach ($list as $k => $v) {
                $list[$k]['component'] = $typeListComponentMap[$v['type']];
            }
            $this->success('ok', $list);
        }
    }

    /**
     * 添加数据
     * @return void
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->getPostData();
            // 构建数据
            $default = array(
              'name' => '',
              'description' => '',
              'value' => '', // 添加时设置为空
              'type' => '',
              'create_user' => '',
              'update_user' => ''
            );
            $data = array_merge($default, $data);
            $data['value'] = $data['value'] ?? '';
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
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 修改数据
     * @return void
     */
    public function edit(): void
    {
        $pk  = $this->model->getPk();
        $id  = $this->request->param($pk);
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->getPostData();
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
                $this->error(__('No rows updated'));
            }
        }

        $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 类型文本
     * @return void
     */
    public function typeText(): void
    {
        $this->success('', [
            'list'   => $this->model->typeListTextMap(),
        ]);
    }
}

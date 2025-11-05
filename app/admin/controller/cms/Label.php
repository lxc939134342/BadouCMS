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
     */
    protected $model;
    protected string|array $quickSearchField = ['id'];
    protected $modelValidate = true;
    protected $noNeedRight = ['getFieldHtml'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Label();

        $this->rules = [
            'name|'.__('name') => 'require|unique:cms_label',
            'type|'.__('type') => 'require',
            'description|'.__('description') => 'require'
        ];
        $typeListTextMap = $this->model->typeListTextMap();
        $this->assign('typeText', $typeListTextMap);
        $this->assignconfig('typeText', $typeListTextMap);
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $res = $this->model
                ->where($where)
                ->where('acode', get_backend_lang())
                ->order($sort, $order)
                ->paginate($limit);
            $this->result('', $res->items(), $res->total());
        }
        return $this->view->fetch();
    }

    // 内容表单
    public function content()
    {
        if ($this->request->isPost()) {
            $postData = $this->getPostData('row/a');

            $list = $this->model->where('acode', get_backend_lang())->column('id,name');
            $data = [];

            foreach ($list as $key => $value) {
                $data[$key]['id'] = $value['id'];
                $data[$key]['name'] = $value['name'];
                $data[$key]['value'] = $postData[$value['name']];
            }

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
        }
        return $this->view->fetch();
    }

    /**
     * 添加数据
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->getPostData('row/a');
            // 构建数据
            $default = array(
              'name' => '',
              'description' => '',
              'value' => '', // 添加时设置为空
              'type' => '',
              'create_user' => $this->auth->nickname,
              'update_user' => $this->auth->nickname,
              'acode' => get_backend_lang()
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
                $this->success(__('Add successful'));
            } else {
                $this->error(__('No rows were added'));
            }
        }
        return $this->view->fetch();
    }

    /**
     * 修改数据
     */
    public function edit($ids = null)
    {
        $row = $this->model->where('id', $ids)->find();
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->getPostData('row/a');
            $data['update_user'] = $this->auth->nickname;
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
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

    public function getFieldHtml()
    {
        $fields = $this->model
            ->where('acode', get_backend_lang())
            ->field('id,name,value,description,type')
            ->order('id', 'desc')
            ->select()->toArray();
        $typeListComponentMap = $this->model->typeListComponentMap();
        $row = [];
        foreach ($fields as $k => $v) {
            $fields[$k]['component'] = $typeListComponentMap[$v['type']];
            $row[$v['name']] = $v['value'];
            $fields[$k]['form_name'] = 'row['.$v['name'].']';
            $fields[$k]['form_id'] = $v['name'];
            $fields[$k]['form_value'] = $row[$v['name']] ??  '';
            $fields[$k]['form_acode'] = '';
        }
        $this->view->assign('custom_fields', $fields);
        $this->view->assign('row', $row);



        $this->success('', null, ['html' => $this->view->fetch('cms/common/builder/fields')]);
    }
}

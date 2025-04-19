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

use app\common\controller\Backend;
use bd\BackendExt;

class Base extends Backend
{
    use BackendExt;

    /**
     * 验证规则 (因为多语言的原因，规则需要在控制器中进行设置)
     * @var array
     */
    protected array $rules = [];

    /**
     * 是否写入管理员
     * @var bool
     */
    protected bool $writeAdmin = true;

    public function initialize(): void
    {
        parent::initialize();

        /* 默认在添加和编辑时写入管理员 */
        if ($this->writeAdmin && $this->request->isPost()) {
            $post = $this->getOriginalInputData();
            $action = $this->request->action();
            if ($action == 'add') {
                $post['create_user'] = $this->auth->nickname;
                $post['update_user'] = $this->auth->nickname;
            }

            if ($action == 'edit') {
                $post['update_user'] = $this->auth->nickname;
            }

            $this->request->withPost($post);
        }
    }

    /**
     * 模型验证方法
     * @param array $data 验证的数据
     * @return void
     */
    protected function modelValidateFunction(array $data): void
    {
        if ($this->modelValidate) {
            $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
            if (class_exists($validate)) {
                $validate = new $validate();
                if ($this->rules) {
                    $validate->rule($this->rules);
                }
                if ($this->modelSceneValidate) {
                    $validate->scene('add');
                }
                $validate->check($data);
            }
        }
    }

    /**
     * 获取原始输入数据 , 防止被多次过滤转义
     * @return array
     */
    protected function getOriginalInputData(): array
    {
        $input = $this->request->getInput();
        return $input ? json_decode($input, true) : [];
    }

    /**
     * 获取post数据
     * @param bool $original 是否获取原始数据
     * @return array
     */
    protected function getPostData($original = false): array
    {
        $data = $original ? $this->getOriginalInputData() : $this->request->post();
        if (!$data) {
            $this->error(__('Parameter %s can not be empty', ['']));
        }

        // 将 null 值转换为空字符串
        array_walk_recursive($data, function (&$value) {
            if ($value === null) {
                $value = '';
            }
        });

        $data = $this->excludeFields($data);
        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $data[$this->dataLimitField] = $this->auth->id;
        }
        return $data;
    }
}

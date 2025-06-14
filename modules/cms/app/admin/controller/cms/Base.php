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

use app\admin\model\cms\Area;
use app\common\controller\Backend;

class Base extends Backend
{
    /**
     * 验证规则 (因为多语言的原因，规则需要在控制器中进行设置)
     * @var array
     */
    protected array $rules = [];

    public function initialize()
    {
        parent::initialize();
        $areaModel = new Area();
        $areaList = $areaModel->areaList();
        $acode = get_backend_lang();
        $this->assign('acode', $acode);
        $this->assign('alist', $areaList);

        $currentArea = array_filter($areaList, function ($item) use ($acode) {
            return $item['acode'] == $acode;
        });
        $currentArea = reset($currentArea);
        if (!$currentArea) {
            $currentArea = $areaModel->defaultArea();
            set_backend_lang($currentArea['acode']);
        }
        $this->assign('area_title', $currentArea['name']);
        $this->assignconfig('acode', $acode);
        $this->assignconfig('alist', $areaList);
    }

    /**
     * 切换语言
     */
    public function changelang()
    {
        $areaModel = new Area();
        $areaList = $areaModel->areaList();
        $acode = $this->request->post('acode');
        if (!in_array($acode, array_column($areaList, 'acode'))) {
            $this->error(__('Invalid parameters'));
        }
        set_backend_lang($acode);
        $this->success('切换语言成功', '', get_backend_lang());
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
     * 强制类型转换
     * @access protected
     * @param  mixed  $data
     * @param  string $type
     * @return mixed
     */
    protected function typeCast(&$data, string $type)
    {
        $data = match (strtolower($type)) {
            'a'     => (array) $data,
            'b'     => (bool) $data,
            'd'     => (int) $data,
            'f'     => (float) $data,
            's'     => is_scalar($data) ? (string) $data : throw new \InvalidArgumentException('variable type error：' . gettype($data)),
            default => $data,
        };
    }

    /**
     * 获取数据
     * @access protected
     * @param  array  $data 数据源
     * @param  string $name 字段名
     * @param  mixed  $default 默认值
     * @return mixed
     */
    protected function getData(array $data, string $name, $default = null)
    {
        foreach (explode('.', $name) as $val) {
            if (isset($data[$val])) {
                $data = $data[$val];
            } else {
                return $default;
            }
        }

        return $data;
    }

    protected function getInputData(): array
    {
        $contentType = $this->request->contentType();
        $input = $this->request->getInput();
        $data = [];
        if ('application/x-www-form-urlencoded' == $contentType) {
            parse_str($input, $data);
            return $data;
        }

        if (str_contains($contentType, 'json')) {
            return (array) json_decode($input, true);
        }

        return [];
    }

    /**
     * 获取原始输入数据 , 防止被多次过滤转义
     * @return array
     */
    protected function getOriginalInputData($name = ''): array
    {
        $data = $this->getInputData();
        $type = '';
        $name = (string) $name;
        if ('' != $name) {
            // 解析name
            if (str_contains($name, '/')) {
                [$name, $type] = explode('/', $name);
            }

            $data = $this->getData($data, $name);
        }
        if ($type) {
            // 强制类型转换
            $this->typeCast($data, $type);
        }
        return $data;
    }

    /**
     * 获取post数据
     * @param string $name
     * @param bool $original 是否获取原始数据
     * @return array
     */
    protected function getPostData($name = '', $original = false): array
    {
        $data = $original ? $this->getOriginalInputData($name) : $this->request->post($name);
        if (!$data) {
            $this->error(__('Parameter %s can not be empty', ['']));
        }

        // 将 null 值转换为空字符串
        array_walk_recursive($data, function (&$value) {
            if ($value === null) {
                $value = '';
            }
        });

        $data = $this->preExcludeFields($data);
        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $data[$this->dataLimitField] = $this->auth->id;
        }
        return $data;
    }
}

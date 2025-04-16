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

use app\admin\model\cms\Models;

/**
 * 模型字段
 */
class Extfield extends Base
{
    /**
     * Extfield模型对象
     * @var \app\admin\model\cms\Extfield
     * @phpstan-var \app\admin\model\cms\Extfield
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['id'];

    protected string $weighField = 'sorting';

    protected string|array $defaultSortField = ['sorting' => 'asc', 'id' => 'desc'];

    protected array $noNeedPermission = ['models', 'typeText','getModelFields'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Extfield();
    }

    public function models(): void
    {
        $models = new Models();
        $list = $models->column('name', 'mcode');
        $this->success('', [
            'list'   => $list,
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

    /**
     * 根据模型获取字段列表
     * @return void
     */
    public function getModelFields(int $mcode): void
    {
        $res = $this->model->getModelFields($mcode);
        $this->success('', $res);
    }
}

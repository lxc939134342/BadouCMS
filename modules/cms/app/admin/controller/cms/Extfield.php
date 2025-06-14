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
    protected $model;

    protected string $weighField = 'sorting';

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Extfield();
        $models = new Models();
        $list = $models->column('name', 'mcode');
        $typeText = $this->model->typeListTextMap();
        $this->view->assign('models', $list);
        $this->view->assign('typeText', $typeText);
        $this->assignconfig('models', $list);
        $this->assignconfig('typeText', $typeText);
    }
}

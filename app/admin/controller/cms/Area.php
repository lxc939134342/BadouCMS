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


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */

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

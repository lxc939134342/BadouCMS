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
 * 会员等级
 */
class MemberGroup extends Base
{
    /**
     * MemberGroup模型对象
     * @var object \app\admin\model\cms\MemberGroup
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected string|array $quickSearchField = ['id'];

    protected array $noNeedPermission = ['getGtypeList','getUserLevel'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\MemberGroup();
    }

    public function getUserLevel()
    {
        return $this->index();
    }

    /**
     * 获取等级类型列表
     */
    public function getGtypeList()
    {
        $typeList = [
            ['value' => '1', 'label' => '小于'],
            ['value' => '2', 'label' => '小于等于'],
            ['value' => '3', 'label' => '等于'],
            ['value' => '4', 'label' => '大于等于'],
            ['value' => '5', 'label' => '大于'],
        ];
        return $this->success('获取成功', ['list' => $typeList]);
    }

}

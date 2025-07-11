<?php

namespace app\admin\model;

use think\Model;

class UserLevel extends Model
{
    protected $name = "user_level";

    public function getGtypeList()
    {
        $typeList = [
            ['value' => '1', 'label' => '小于'],
            ['value' => '2', 'label' => '小于等于'],
            ['value' => '3', 'label' => '等于'],
            ['value' => '4', 'label' => '大于等于'],
            ['value' => '5', 'label' => '大于'],
        ];
        return $typeList;
    }

    /**
     * 获取等级选择列表
     */
    public function getLevelList()
    {
        return $this->where('status', 1)->field('id,gcode,gname')
            ->order('gcode,id')
            ->select();
    }
}

<?php

namespace app\admin\model;

use think\Model;

class UserGroup extends Model
{
    protected $autoWriteTimestamp = true;

    // 追加属性
    protected $append = [
        'status_text'
    ];

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }
}
<?php

namespace app\admin\model;

use think\Model;
use think\model\relation\BelongsTo;

class User extends Model
{
    protected $autoWriteTimestamp = true;

    protected $append = [
        'avatar_txt',
        'prevtime_text',
        'logintime_text',
        'jointime_text'
    ];

    protected $hidden = [
        'password',
        'token',
    ];

    public function getGenderList()
    {
        return ['1' => __('Male'), '0' => __('Female')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getAvatarTxtAttr($value, $data)
    {
        $value = $value ? $value : $data['avatar'];
        return $value ? cdnurl($value, true) : letter_avatar($data['nickname']);
    }

    public function getPrevtimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['prevtime'] ?? "");
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getLogintimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['logintime'] ?? "");
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo('UserGroup', 'group_id', 'id');
    }
}
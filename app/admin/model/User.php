<?php

namespace app\admin\model;

use think\Model;
use think\model\relation\BelongsTo;

class User extends Model
{
    protected $autoWriteTimestamp = true;

    protected $append = [
        'avatar_txt'
    ];

    protected $hidden = [
        'password',
        'token',
    ];

    public function getAvatarTxtAttr($value, $data)
    {
        $value = $value ? $value : $data['avatar'];
        return $value ? cdnurl($value, true) : letter_avatar($data['nickname']);
    }

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo('UserGroup', 'group_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
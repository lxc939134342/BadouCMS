<?php

namespace app\index\model\cms;

use think\Model;
use app\admin\model\UserGroup;
use think\model\relation\HasOne;
use think\model\relation\BelongsTo;
use app\admin\model\cms\MemberGroup;

class User extends Model
{
    protected $name = "user";
    /**
    * 允许输出的字段
    * @var array
    */
    protected $hidden = ['password', 'salt', 'status', 'token', 'refresh_token','create_time','update_time'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class, 'group_id');
    }

    /**
     * 会员等级
     */
    public function memberGroup(): HasOne
    {
        return $this->hasOne(MemberGroup::class, 'id', 'level');
    }

    /**
     * 获取用户信息
     * @param mixed $user_id
     */
    public function getUserInfo($user_id): array
    {
        $info = $this
            ->with(['group','member_group'])
            ->where('id', $user_id)->find();
        if (!$info) {
            return [];
        }
        $info = $info->toArray();

        $info['group_name'] = $info['group']['name'] ?? '';
        $info['gcode'] = $info['member_group']['gcode'] ?? '';
        $info['gname'] = $info['member_group']['gname'] ?? '';

        return $info;
    }
}

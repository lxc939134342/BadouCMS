<?php

namespace app\admin\model;

use app\admin\model\cms\MemberGroup;
use ba\Random;
use think\Model;
use think\model\relation\BelongsTo;
use think\model\relation\HasOne;

/**
 * User 模型
 */
class User extends Model
{
    protected $autoWriteTimestamp = true;

    public function getAvatarAttr($value): string
    {
        return full_url($value, false, config('buildadmin.default_avatar'));
    }

    public function setAvatarAttr($value): string
    {
        return $value == full_url('', false, config('buildadmin.default_avatar')) ? '' : $value;
    }

    public function getMoneyAttr($value): string
    {
        return bcdiv($value, 100, 2);
    }

    public function setMoneyAttr($value): string
    {
        return bcmul($value, 100, 2);
    }

    /**
     * 重置用户密码
     * @param int|string $uid         用户ID
     * @param string     $newPassword 新密码
     * @return int|User
     */
    public function resetPassword(int|string $uid, string $newPassword): int|User
    {
        $salt   = Random::build('alnum', 16);
        $passwd = encrypt_password($newPassword, $salt);
        return $this->where(['id' => $uid])->update(['password' => $passwd, 'salt' => $salt]);
    }

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
}

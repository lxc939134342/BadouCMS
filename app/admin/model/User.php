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

namespace app\admin\model;

use think\Model;
use app\common\model\MoneyLog;
use app\common\model\ScoreLog;
use think\model\relation\BelongsTo;
use app\common\library\FrontendAuth;

class User extends Model
{
    protected $autoWriteTimestamp = true;

    protected $type = [
        'create_time' => 'int',
        'update_time' => 'int'
    ];

    protected $append = [
        'prevtime_text',
        'logintime_text',
        'jointime_text'
    ];

    protected $hidden = [
        'password',
        'token',
    ];

    public static function onBeforeWrite($row)
    {
        $changeData = $row->getChangedData();

        if (!array_key_exists('password', $changeData)) {
            return;
        }

        $password = $changeData['password'];

        if ($password === null || $password === '') {
            $row->password = $row->getOrigin('password');
            return;
        }

        $row->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public static function onBeforeUpdate($row)
    {
        $changedata = $row->getChangedData();
        $origin = $row->getOrigin();
        if (isset($changedata['money']) && (function_exists('bccomp') ? bccomp($changedata['money'], $origin['money'], 2) !== 0 : (float)$changedata['money'] !== (float)$origin['money'])) {
            MoneyLog::create(['user_id' => $row['id'], 'money' => $changedata['money'] - $origin['money'], 'before' => $origin['money'], 'after' => $changedata['money'], 'memo' => '管理员变更金额']);
        }
        if (isset($changedata['score']) && (int)$changedata['score'] !== (int)$origin['score']) {
            ScoreLog::create(['user_id' => $row['id'], 'score' => $changedata['score'] - $origin['score'], 'before' => $origin['score'], 'after' => $changedata['score'], 'memo' => '管理员变更积分']);
        }
    }

    public function getGenderList()
    {
        return ['1' => __('Male'), '0' => __('Female')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function setBirthdayAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    public function setPrevtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }
    public function getPrevtimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['prevtime'] ?? "");
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function setLogintimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }
    public function getLogintimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['logintime'] ?? "");
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function setJointimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }
    public function getJointimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['jointime'] ?? "");
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class, 'group_id', 'id');
    }


    /**
     * 重置用户密码
     * @param int|string $uid         管理员ID
     * @param string     $newPassword 新密码
     */
    public function resetPassword(int|string $uid, string $newPassword)
    {
        $auth = new FrontendAuth();
        $passwd = $auth->getEncryptPassword($newPassword);
        return $this->where(['id' => $uid])->update(['password' => $passwd]);
    }
}

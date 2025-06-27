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

use app\common\library\AdminAuth;
use think\Model;

class Admin extends Model
{
    /**
     * @var string 自动写入时间戳
     */
    protected $autoWriteTimestamp = true;

    protected $name  = 'admin';

    protected $hidden = [
        'password'
    ];

    public function getLastLoginTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    public static function onBeforeWrite($row)
    {
        $changed = $row->getChangedData();
        //如果修改了用户或或密码则需要重新登录
        if (isset($changed['username']) || isset($changed['password']) || isset($changed['salt'])) {
            $row->token = '';
        }
    }

    /**
     * 重置用户密码
     * @param int|string $uid         管理员ID
     * @param string     $newPassword 新密码
     * @return int|Admin
     */
    public function resetPassword(int|string $uid, string $newPassword): int|Admin
    {
        $auth = new AdminAuth();
        $passwd = $auth->getEncryptPassword($newPassword);
        return $this->where(['id' => $uid])->update(['password' => $passwd]);
    }
}

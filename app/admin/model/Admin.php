<?php

namespace app\admin\model;

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


}
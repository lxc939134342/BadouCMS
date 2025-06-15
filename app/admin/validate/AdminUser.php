<?php
// +----------------------------------------------------------------------
// | BadouAdmin [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://badouadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: badouDev <877732602@qq.com>
// +----------------------------------------------------------------------
// | Original reference: https://gitee.com/lande_admin/badouadmin
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// |  管理员管理验证类
// +----------------------------------------------------------------------
namespace app\admin\validate;

use \think\Validate;
/**
 * 角色组验证器
 */
class AdminUser extends Validate
{
    //定义验证规则
    protected $rule = [
        'username|用户名' => 'unique:admin|require|alphaDash|length:3,20',
        'password|密码'  => 'require|length:3,20|confirm',
        'email|邮箱'     => 'email|unique:admin',
        'mobile|手机'    => 'mobile|unique:admin',
        'group|权限组'    => 'require',
    ];

    // 登录验证场景定义
    public function sceneUpdate()
    {
        return $this->only(['username', 'password', 'email', 'mobile'])
            ->remove('password', 'require');
    }

    //定义验证场景
    protected $scene = [
        'insert' => ['username', 'password', 'email', 'mobile'],

    ];
}
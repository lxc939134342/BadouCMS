<?php

namespace app\common\model;

use think\Model;

/**
 * @method static mixed getByUsername($str) 通过用户名查询用户
 * @method static mixed getByNickname($str) 通过昵称查询用户
 * @method static mixed getByMobile($str) 通过手机查询用户
 * @method static mixed getByEmail($str) 通过邮箱查询用户
 * Class User
 * @package app\common\model
 * Author: Wusn <958342972@qq.com>
 * DateTime: 2025/6/22 20:15
 */
class User extends Model
{

    // 追加属性
    protected $append = [
        'url',
    ];

    public function getUrlAttr($value, $data)
    {
        return "/u/" . $data['id'];
    }

    public function getAvatarAttr($value, $data)
    {
        if (!$value) {
            //如果不需要启用首字母头像，请使用
            //$value = '/assets/img/avatar.png';
            $value = letter_avatar($data['nickname']);
        }
        return $value;
    }

}

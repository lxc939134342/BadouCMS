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

namespace app\common\library;

use badou\Email;
use badou\Random;
use think\facade\Event;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * 邮箱验证码类
 */
class Ems
{
    /**
     * 验证码有效时长
     * @var int
     */
    protected static $expire = 120;

    /**
     * 最大允许检测的次数
     * @var int
     */
    protected static $maxCheckNums = 10;

    /**
     * 获取最后一次邮箱发送的数据
     *
     * @param int    $email 邮箱
     * @param string $event 事件
     * @return  Ems|null
     */
    public static function get($email, $event = 'default')
    {
        $ems = \app\common\model\Ems::where(['email' => $email, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        if ($ems) {
            $ems = $ems->toArray();
        }
        Event::trigger('ems_get', $ems);
        return $ems ?: null;
    }

    /**
     * 发送验证码
     *
     * @param int    $email 邮箱
     * @param int    $code  验证码,为空时将自动生成4位数字
     * @param string $event 事件
     * @return  boolean
     */
    public static function send($email, $code = null, $event = 'default')
    {
        $code = is_null($code) ? Random::build('numeric', config('captcha.length')) : $code;
        $time = time();
        $ip = request()->ip();
        $ems = \app\common\model\Ems::create(['event' => $event, 'email' => $email, 'code' => $code, 'ip' => $ip, 'create_time' => $time]);
        if (!Event::hasListener('ems_send')) {
            //采用框架默认的邮件推送
            Event::listen('ems_send', function ($params) {
                $result = true;
                try {
                    $mail = new Email();
                    $mail->isSMTP();
                    $mail->addAddress($params->email);
                    $mail->setSubject('请查收你的验证码！');
                    $mail->message("你的验证码是：" . $params->code . "，" . ceil(self::$expire / 60) . "分钟内有效。")
                        ->send();
                } catch (PHPMailerException $e) {
                    $result = false;
                }
                return $result;
            });
        }
        $result = Event::trigger('ems_send', $ems, true);
        if (!$result) {
            $ems->delete();
            return false;
        }
        return true;
    }

    /**
     * 发送通知
     *
     * @param mixed  $email    邮箱,多个以,分隔
     * @param string $msg      消息内容
     * @param string $template 消息模板
     * @return  boolean
     */
    public static function notice($email, $msg = '', $template = null)
    {
        $params = [
            'email'    => $email,
            'msg'      => $msg,
            'template' => $template
        ];
        if (!Event::hasListener('ems_notice')) {
            Event::listen('ems_notice', function ($params) {
                $subject = '你收到一封新的邮件！';
                $content = $params['msg'];
                $mail = new Email();
                $mail->isSMTP();
                $mail->addAddress($params['email']);
                $mail->setSubject($subject);
                $result = $mail
                    ->message($content)
                    ->send();
                return $result;
            });
        }
        $result = Event::trigger('ems_notice', $params);
        return (bool)$result;
    }

    /**
     * 校验验证码
     *
     * @param int    $email 邮箱
     * @param int    $code  验证码
     * @param string $event 事件
     * @return  boolean
     */
    public static function check($email, $code, $event = 'default')
    {
        $time = time() - self::$expire;
        $ems = \app\common\model\Ems::where(['email' => $email, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        if ($ems) {
            if ($ems['create_time'] > $time && $ems['times'] <= self::$maxCheckNums) {
                $correct = $code == $ems['code'];
                if (!$correct) {
                    $ems->times = $ems->times + 1;
                    $ems->save();
                    return false;
                } else {
                    Event::trigger('ems_check', $ems);
                    return true;
                }
            } else {
                // 过期则清空该邮箱验证码
                self::flush($email, $event);
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 清空指定邮箱验证码
     *
     * @param int    $email 邮箱
     * @param string $event 事件
     * @return  boolean
     */
    public static function flush($email, $event = 'default')
    {
        \app\common\model\Ems::where(['email' => $email, 'event' => $event])
            ->delete();
        Event::trigger('ems_flush');
        return true;
    }
}

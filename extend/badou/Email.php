<?php

namespace badou;

use Throwable;
use think\facade\Lang;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * 邮件类
 * 继承PHPMailer并初始化好了站点系统配置中的邮件配置信息
 */
class Email extends PHPMailer
{
    /**
     * 是否已在管理后台配置好邮件服务
     * @var bool
     */
    public bool $configured = false;

    /**
     * 默认配置
     * @var array
     */
    public array $options = [
        'charset' => 'utf-8', //编码格式
        'debug'   => true, //调式模式
        'lang'    => 'zh_cn',
    ];

    /**
     * 构造函数
     * @param array $options
     * @throws Throwable
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);

        parent::__construct($this->options['debug']);
        $langSet = Lang::getLangSet();
        if ($langSet == 'zh-cn' || !$langSet) {
            $langSet = 'zh_cn';
        }
        $this->options['lang'] = $this->options['lang'] ?: $langSet;

        $this->setLanguage($this->options['lang'], root_path() . 'vendor' . DIRECTORY_SEPARATOR . 'phpmailer' . DIRECTORY_SEPARATOR . 'phpmailer' . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR);
        $this->CharSet = $this->options['charset'];

        $sysMailConfig    = get_sys_config('', 'mail');
        $this->configured = true;
        foreach ($sysMailConfig as $item) {
            if ($item === '' || is_null($item)) {
                $this->configured = false;
            }
        }
        if (!$this->configured) {
            throw new PHPMailerException("邮箱配置信息邮件不完整，请先在管理后台配置好邮件服务");
        }
        $this->Host       = $sysMailConfig['smtp_server'];
        $this->SMTPAuth   = true;
        $this->Username   = $sysMailConfig['smtp_user'];
        $this->Password   = $sysMailConfig['smtp_pass'];
        $this->SMTPSecure = $sysMailConfig['smtp_verification'] == 'SSL' ? self::ENCRYPTION_SMTPS : self::ENCRYPTION_STARTTLS;
        $this->Port       = $sysMailConfig['smtp_port'];

        $this->setFrom($sysMailConfig['smtp_sender_mail'], $sysMailConfig['smtp_user']);
    }

    /**
     * 设置邮件正文
     * @param string  $body   邮件下方
     * @param boolean $ishtml 是否HTML格式
     * @return $this
     */
    public function message($body, $ishtml = true)
    {
        $this->isHTML($ishtml);
        $this->Body = $body;
        return $this;
    }

    public function setSubject($subject): void
    {
        $this->Subject = "=?utf-8?B?" . base64_encode($subject) . "?=";
    }
}

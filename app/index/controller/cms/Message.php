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

namespace app\index\controller\cms;

use ba\Captcha;
use app\common\library\Email;
use app\index\model\cms\Form;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Message extends Base
{
    protected array $noNeedLogin = ['index', 'submitForm'];

    /**
     * @var Form
     */
    protected object $model;

    public function initialize(): void
    {
        parent::initialize();

        $this->model = new Form();
    }

    protected function createFormData(int $fcode, array $data)
    {
        $this->request->filter('clean_xss');
        $post = $this->request->post();
        // 留言 或 表单
        $formTypeText = $fcode == 1 ? __('message') : __('form');

        if (time() - session('lastsub') < 10) {
            // 您提交太频繁了，请稍后再试！
            $this->error(__('SubmitTooFrequent'));
        }
        // 读取字段
        if (! $form = $this->model->getFormField($fcode)) {
            // 表单不存在任何字段
            $this->error(__('%s does not exist any field, please check and try again!', [$formTypeText]));
        }


        // 接收数据
        $mail_body = '';
        foreach ($form as $value) {
            $field_data = $post[$value['name']] ?? '';
            if (is_array($field_data)) { // 如果是多选等情况时转换
                $field_data = implode(',', $field_data);
            }
            if ($value['required'] && ! $field_data) {
                $this->error(__('%s cannot be empty', [__($value['name'])]));
            } else {
                $data[$value['name']] = $field_data;
                $mail_body .= $value['description'] . '：' . $field_data . '<br>';
            }
        }

        if ($this->model->addTableData($fcode, $data)) {
            session('lastsub', time()); // 记录最后提交时间
            trace($formTypeText.'提交成功！', 'log');
            if (get_sys_config('message_send_mail') && get_sys_config('message_send_to')) {
                $mail   = new Email();
                if (!$mail->configured) {
                    // 邮件发送服务不可用
                    $this->error(__('Mail sending service unavailable'));
                }
                $mail_subject = "【" . get_sys_config('site_name') . "】您有新的". $value['form_name'] . "信息，请注意查收！";
                $mail_body .= '<br>来自网站 ' . $this->request->domain() . ' （' . date('Y-m-d H:i:s') . '）';
                try {
                    $mail->isSMTP();
                    $mail->addAddress(get_sys_config('message_send_to'));
                    $mail->isHTML();
                    $mail->setSubject($mail_subject);
                    $mail->Body = $mail_body;
                    $mail->send();
                } catch (PHPMailerException) {
                    $this->error($mail->ErrorInfo);
                }
            }

            $this->success(__('SubmitSuccess'));
        } else {
            trace($formTypeText.'提交失败！', 'error');
            $this->error(__('SubmitFailed'));
        }
    }

    /* 留言提交 */
    public function index()
    {
        if (!$this->request->isPost()) {
            abort(404, __('NotUrl'));
        }
        // 开启留言
        if (!get_sys_config('message_status')) {
            $this->error(__('%s StatusClose', [__('Message')]));
        }
        // 需登录
        if (get_sys_config('message_rqlogin') && ! $this->auth->isLogin()) {
            $this->error(__('Please login first'), url('user/login'));
        }
        $data = [];

        // 验证码验证
        if (get_sys_config('message_check_code')) {
            // 验证码验证
            // 接受验证码和验证码ID
            $data['captcha']   = $this->request->post('captcha');
            $data['captchaId'] = $this->request->post('captcha_id');

            $captchaObj = new Captcha();
            if (!$captchaObj->check($data['captcha'], $data['captchaId'])) {
                // 验证码错误！
                $this->error(__('CaptchaError'));
            }
        }

        $status = get_sys_config('message_verify') === '0' ? 1 : 0;

        // 设置额外数据
        $data['acode'] = get_frontend_lang();
        $data['user_ip'] = ip2long(get_user_ip());
        $data['user_os'] = get_user_os();
        $data['user_bs'] = get_user_bs();
        $data['recontent'] = '';
        $data['status'] = $status;
        $data['create_user'] = 'guest';
        $data['update_user'] = 'guest';
        $data['uid'] = $this->auth->id ?? '';
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');

        $this->createFormData(1, $data);
    }

    /* 自定义表单 */
    public function submitForm()
    {
        if (!$this->request->isPost()) {
            abort(404, __('NotUrl'));
        }

        // 开启表单
        if (!get_sys_config('form_status')) {
            $this->error(__('%s StatusClose', [__('Form')]));
        }

        $fcode = $this->request->param('fcode/d');
        if ($fcode == 1) {
            // 表单提交地址有误，留言提交请使用留言专用地址!
            $this->error(__('FormSubmitAddressError'));
        }
        $data = [
            'acode' => get_frontend_lang(),
            'create_time' => date('Y-m-d H:i:s'),
        ];

        $this->createFormData($fcode, $data);
    }


}

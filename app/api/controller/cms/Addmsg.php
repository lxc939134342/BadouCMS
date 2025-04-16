<?php

namespace app\api\controller\cms;

use app\common\library\Email;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Addmsg extends Base
{
    protected array $noNeedLogin = ['*'];
    protected $model = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\index\model\cms\Form();
    }

    public function index()
    {
        if (!$this->request->isPost()) {
            $this->error('留言提交失败，请使用POST方式提交！');
        }
        $this->request->filter('clean_xss');
        $post = $this->request->post();
        // 开启留言
        if (!get_sys_config('message_status')) {
            $this->error(__('%s StatusClose', [__('Message')]));
        }

        // 读取字段
        if (! $form = $this->model->getFormField(1)) {
            // 表单不存在任何字段
            $this->error(__('%s does not exist any field, please check and try again!', [__('message')]));
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

        if ($this->model->addTableData(1, $data)) {
            session('lastsub', time()); // 记录最后提交时间
            trace('API留言提交成功！', 'log');
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
            trace('API留言提交失败！', 'error');
            $this->error(__('SubmitFailed'));
        }
    }
}

<?php

namespace app\api\controller\cms;

use app\common\library\Email;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Addform extends Base
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
            $this->error('表单提交失败，请使用POST方式提交！');
        }
        $this->request->filter('clean_xss');
        $post = $this->request->post();

        // 开启表单
        if (!get_sys_config('form_status')) {
            $this->error(__('%s StatusClose', [__('Form')]));
        }

        $fcode = $this->request->param('fcode/d');
        if (!$fcode) {
            $this->error('传递的表单编码fcode有误！');
        }
        if ($fcode == 1) {
            // 表单提交地址有误，留言提交请使用留言专用地址!
            $this->error(__('FormSubmitAddressError'));
        }

        // 读取字段
        if (! $form = $this->model->getFormField($fcode)) {
            // 表单不存在任何字段
            $this->error(__('%s does not exist any field, please check and try again!', [__('Form')]));
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

        // 设置额外数据
        $data['acode'] = get_frontend_lang();
        $data['create_time'] = date('Y-m-d H:i:s');

        if ($this->model->addTableData($fcode, $data)) {
            session('lastsub', time()); // 记录最后提交时间
            trace('API表单提交成功！', 'log');
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
            trace('API表单提交失败！', 'error');
            $this->error(__('SubmitFailed'));
        }
    }
}

<?php

namespace app\admin\controller\general;

use Throwable;
use badou\Email;
use app\common\controller\Backend;
use PHPMailer\PHPMailer\PHPMailer;
use app\admin\model\Config as ConfigModel;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Config extends Backend
{
    /**
     * @var ConfigModel
     */
    protected $model = null;
    protected $noNeedRight = ['check', 'rulelist', 'selectpage', 'get_fields_list'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new ConfigModel();
    }

    public function index()
    {
        $configGroup = get_sys_config('config_group');
        $config      = $this->model->order('weigh desc')->select()->toArray();

        $list           = [];
        $newConfigGroup = [];
        foreach ($configGroup as $item) {
            $list[$item['key']]['name']   = $item['key'];
            $list[$item['key']]['title']  = __($item['value']);
            $newConfigGroup[$item['key']] = $list[$item['key']]['title'];
        }
        foreach ($config as $item) {
            if (array_key_exists($item['group'], $newConfigGroup)) {
                $item['title']                  = __($item['title']);
                $list[$item['group']]['list'][] = $item;
            }
        }

        $this->assign('config_group', $newConfigGroup);
        $this->assign('list', $list);

        return $this->view->fetch();
    }

    /**
     * 编辑
     * @throws Throwable
     */
    public function edit(): void
    {
        $all = $this->model->select();
        foreach ($all as $item) {
            if ($item['type'] == 'editor') {
                $this->request->filter('clean_xss');
                break;
            }
        }
        if ($this->request->isPost()) {
            $this->modelValidate = false;
            $data                = $this->request->post("row/a", [], 'trim');
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->preExcludeFields($data);
            $configValue = [];
            foreach ($all as $item) {
                if (array_key_exists($item->name, $data)) {
                    $configValue[] = [
                        'id'    => $item->id,
                        'type'  => $item->getData('type'),
                        'value' => $data[$item->name]
                    ];
                }
            }

            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) {
                            $validate->scene('edit');
                        }
                        $validate->check($data);
                    }
                }
                $result = $this->model->saveAll($configValue);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }
    }


    /**
     * 发送邮件测试
     * @throws Throwable
     */
    public function sendTestMail(): void
    {
        $data = $this->request->post('row/a', '', 'trim');
        if (!$data) {
            $this->error(__('Parameter %s can not be empty', ['']));
        }
        $mail = new Email();
        try {
            $mail->Host       = $data['smtp_server'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $data['smtp_user'];
            $mail->Password   = $data['smtp_pass'];
            $mail->SMTPSecure = $data['smtp_verification'] == 'SSL' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $data['smtp_port'];

            $mail->setFrom($data['smtp_sender_mail'], $data['smtp_user']);

            $mail->isSMTP();
            $mail->addAddress($data['test_email']);
            $mail->isHTML();
            $mail->setSubject(__('This is a test email') . '-' . get_sys_config('site_name'));
            $mail->Body = __('The mail service has been configured correctly');
            $mail->send();
        } catch (PHPMailerException) {
            $this->error($mail->ErrorInfo);
        }
        $this->success(__('Test mail sent successfully~'));
    }
}

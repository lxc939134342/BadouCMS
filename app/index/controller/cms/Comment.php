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
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use app\index\model\cms\MemberComment as MemberCommentModel;

class Comment extends Base
{
    /**
     * 评论模型
     * @var MemberCommentModel
     */
    protected object $model;
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new MemberCommentModel();
        $this->userInitialize();
    }

    /**
     * 评论提交
     * @return void
     */
    public function add(): void
    {
        if ($this->request->isPost()) {

            /* 系统是否开启评论 */
            if (get_sys_config('commentstatus') === '0') {
                $this->error(__('%s StatusClose', [__('Comment')]));
            }

            /* 提交限流 */
            if (time() - session('lastsub') < 10) {
                $this->error(__('SubmitTooFrequent'));
            }


            // 验证码验证
            // 接受验证码和验证码ID
            $data['captcha']   = $this->request->post('captcha');
            $data['captchaId'] = $this->request->post('captcha_id');
            if (!$data['captchaId']) {
                $data['captchaId'] = session('captchaId');
            }

            $captchaObj = new Captcha();
            if (!$captchaObj->check($data['captcha'], $data['captchaId'])) {
                // 验证码错误！
                $this->error(__('CaptchaError'));
            }

            // 接收数据
            /* 评论是否需要审核 */
            $status = get_sys_config('comment_verify') ? 0 : 1;

            $contentid = $this->request->param('contentid/d', 0);
            if (!$contentid) {
                $this->error(__('ArticleIdNotNormal'));
            }

            $comment = $this->request->post('comment');
            // 评论内容不能为空
            if (empty($comment)) {
                $this->error(__('CommentContentEmpty'));
            }

            $data = array(
                'pid' => $this->request->param('pid/d', 0),
                'contentid' => $contentid,
                'comment' => $comment,
                'uid' => $this->auth->id,
                'puid' => $this->request->param('puid/d', 0),
                'likes' => 0,
                'oppose' => 0,
                'status' => $status,
                'user_ip' => ip2long(get_user_ip()),
                'user_os' => get_user_os(),
                'user_bs' => get_user_bs(),
                'update_user' => '',
            );


            if ($this->model->save($data)) {
                session('lastsub', time()); // 记录最后提交时间

                if (get_sys_config('comment_send_mail') && get_sys_config('message_send_to')) {
                    $mail   = new Email();
                    if (!$mail->configured) {
                        // 邮件发送服务不可用
                        $this->error(__('Mail sending service unavailable'));
                    }
                    $mail_subject = "【" . get_sys_config('site_name') . "】您有新的文章评论信息，请注意查收！";
                    $mail_body = '评论内容：' . $comment . '<br>';
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
                if ($status) {
                    $this->success(__('SubmitSuccess'));
                } else {
                    // 评论提交成功,请等待管理员审核！
                    $this->success(__('SubmitSuccessWaitReview'));
                }
            } else {
                trace('文章评论提交失败！', 'error');
                // 提交失败！
                $this->error(__('SubmitFailed'));
            }
        } else {
            // 提交失败！
            $this->error(__('SubmitFailed'));
        }
    }

    /**
     * 我的评论
     * @return string
     */
    public function mycomment(): string
    {
        if ($this->request->isPost()) {
            $res   = $this->model
                ->where('uid', $this->auth->id)
                ->with(['content'])
                ->order('create_time desc')
                ->paginate([
                    'page' => $this->request->param('page/d', 1),
                    'list_rows' => $this->request->param('limit/d', 10),
                ]);

            $this->success('', '', [
                'list'  => $res->items(),
                'total' => $res->total(),
            ]);
        }
        return $this->view->fetch('user/mycomment');
    }

    /**
     * 删除评论
     * @return void
     */
    public function delete(): void
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id/d', 0);
            if ($id) {
                $this->model->where('id', $id)->delete();
                $this->success(__('DeleteSuccess'));
            }
        }
        $this->error(__('DeleteFailed'));
    }
}

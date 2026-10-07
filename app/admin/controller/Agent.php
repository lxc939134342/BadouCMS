<?php

namespace app\admin\controller;

use app\common\agent\AgentContext;
use app\common\agent\AgentRunControl;
use app\common\agent\AdminAgent;
use app\admin\model\AgentStorage;
use app\common\agent\services\AgentPatchService;
use app\common\ai\AiException;
use app\common\ai\AiGateway;
use app\admin\model\AgentChat;
use app\common\controller\Backend;
use app\common\agent\AgentService;
use badou\Server;
use think\facade\Log;
use app\common\agent\model\AgentTask;
use app\common\agent\queue\AgentTaskService;

class Agent extends Backend
{
    protected $noNeedRight = ['syncAccount', 'balance', 'packages', 'order', 'orderStatus', 'recharge', 'taskStatus'];

    public function index()
    {
        $this->assign('canSend', $this->auth->check('agent/send'));
        $this->assign('adminId', (int)$this->auth->id);
        $this->assign('drawerMode', (int)$this->request->get('drawer/d', 0) === 1);
        $this->assign('modelReady', AiGateway::ready());
        $this->assign('canEditUsers', $this->auth->check('user.user/edit'));
        $this->assign('isSuperAdmin', $this->auth->isSuperAdmin());
        $this->assignconfig([
            'api_url' => rtrim((string)config('badouadmin.api_url'), '/'),
            'bdversion' => config('badouadmin.version'),
        ]);
        return $this->view->fetch();
    }

    public function balance()
    {
        if (!$this->auth->check('agent/index')) {
            $this->error('无权查看 AI 额度');
        }
        try {
            $balance = AiGateway::balance();
        } catch (AiException $e) {
            $this->error($e->getMessage());
        }
        $this->success('获取成功', '', $balance);
    }

    public function packages()
    {
        if (!$this->auth->check('agent/index')) {
            $this->error('无权购买 AI 额度');
        }
        try {
            $packages = AiGateway::packages();
        } catch (AiException $e) {
            $this->error($e->getMessage(), null, ['need_login' => $e->reason === AiException::NOT_LOGIN]);
        }
        $this->success('获取成功', '', ['packages' => $packages]);
    }

    public function recharge()
    {
        if (!$this->auth->check('agent/index')) {
            $this->error('无权购买 AI 额度');
        }
        return $this->view->fetch();
    }

    public function order()
    {
        if (!$this->auth->check('agent/index')) {
            $this->error('无权购买 AI 额度');
        }
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $packageId = (string)$this->request->post('package_id', '');
        $paytype = (string)$this->request->post('paytype', '');
        if (!preg_match('/^[a-z0-9_-]{1,30}$/', $packageId)
            || !in_array($paytype, ['alipay', 'wechat'], true)) {
            $this->error('请选择有效的 AI 套餐和支付方式');
        }
        try {
            $order = AiGateway::createOrder($packageId, $paytype);
        } catch (AiException $e) {
            $this->error($e->getMessage(), null, ['need_login' => $e->reason === AiException::NOT_LOGIN]);
        }
        $this->success('订单创建成功', '', $order);
    }

    public function orderStatus()
    {
        if (!$this->auth->check('agent/index')) {
            $this->error('无权查看 AI 订单');
        }
        $orderNo = (string)$this->request->get('order_no', '');
        if (!preg_match('/^AI[0-9]{14}[a-f0-9]{10}$/', $orderNo)) {
            $this->error('订单号无效');
        }
        try {
            $status = AiGateway::orderStatus($orderNo);
        } catch (AiException $e) {
            $this->error($e->getMessage());
        }
        $this->success('获取成功', '', $status);
    }

    public function syncAccount()
    {
        if (!$this->auth->check('agent/index')) {
            $this->error('无权同步 AI 助手登录状态');
        }
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $uid = (int)$this->request->post('uid');
        $token = trim((string)$this->request->post('token'));
        if ($uid <= 0 || $token === '') {
            $this->error('插件市场登录信息无效，请重新登录');
        }
        try {
            Server::saveUserInfo($uid, $token);
        } catch (\Throwable $e) {
            $this->error('登录状态同步失败，请重试');
        }
        $this->success('登录状态同步成功');
    }

    public function sessions()
    {
        $list = AgentChat::listForUser($this->tenantId(), (int)$this->auth->id);
        $this->success('获取成功', '', ['list' => $list]);
    }

    public function history()
    {
        $chat = $this->ownedChat((int)$this->request->get('id/d', 0));
        $agent = new AdminAgent($this->chatContext($chat));
        $service = new AgentService();
        try {
            $reply = $service->recover($agent);
            if ($reply !== null) {
                $chat->recordReply($reply, 0);
                $service->acknowledge($agent, $reply);
            }
            $approval = $service->approval($agent);
        } catch (\Throwable $e) {
            $this->error('暂时无法恢复会话，请稍后重试');
        }
        $this->success('获取成功', '', ['id' => (int)$chat->id, 'messages' => $chat->transcript(), 'revision' => $chat->revision(), 'approval' => $approval]);
    }

    public function send()
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        if (in_array($this->request->post('action/s', ''), ['task_cancel', 'task_retry', 'task_wake'], true)) {
            try {
                $context = AgentContext::fromAuth($this->auth);
                $taskId = (int)$this->request->post('task_id/d', 0);
                if ($this->request->post('action/s', '') === 'task_wake') {
                    (new AgentTaskService())->wake($context, $taskId);
                } elseif ($this->request->post('action/s', '') === 'task_retry') {
                    $task = AgentTask::retry($taskId, $context->tenantId, $context->adminId);
                    (new AgentTaskService())->trigger($task);
                } else {
                    AgentTask::cancel($taskId, $context->tenantId, $context->adminId);
                }
            } catch (\DomainException $e) { $this->error($e->getMessage()); }
            $this->success('任务状态已更新，已完成的批次保留');
        }
        $requestId = (string)$this->request->post('request_id/s', '');
        try {
            $control = $requestId !== '' ? new AgentRunControl($this->tenantId(), (int)$this->auth->id, $requestId) : null;
            if ($this->request->post('action/s', '') === 'stop') {
                if ($control === null) throw new \DomainException('Agent 请求标识无效');
                $control->stop();
            }
        } catch (\DomainException $e) {
            $this->error($e->getMessage());
        }
        if ($this->request->post('action/s', '') === 'stop') $this->success('正在停止');
        $question = trim((string)$this->request->post('message/s', ''));
        $decision = (string)$this->request->post('decision/s', '');
        $approval = $decision === '' ? null : [
            'decision' => $decision, 'run_id' => (string)$this->request->post('run_id/s', ''),
            'attempt' => (int)$this->request->post('attempt/d', 0),
        ];
        if ($approval !== null && !in_array($decision, ['approve', 'reject', 'refresh'], true)) $this->error('审批决策无效');
        if ($approval === null && ($question === '' || mb_strlen($question) > 3000)) {
            $this->error('请输入 1 至 3000 字的问题');
        }
        $chatId = (int)$this->request->post('id/d', 0);
        if ($approval !== null && $chatId <= 0) $this->error('请选择需要审批的会话');
        $autoApprove = (bool)$this->request->post('auto_approve/d', 0);
        $chat = $chatId > 0 ? $this->ownedChat($chatId) : null;
        $editMessageId = (string)$this->request->post('edit_message_id/s', '');
        $editRevision = (string)$this->request->post('edit_revision/s', '');
        $editing = $editMessageId !== '' || $editRevision !== '';
        if ($editing) {
            if (!$chat || $approval !== null || !preg_match('/^[a-f0-9]{32}$/D', $editMessageId)
                || !preg_match('/^[a-f0-9]{64}$/D', $editRevision)) $this->error('编辑消息参数无效，请刷新对话');
            try {
                $sourceAgent = new AdminAgent($this->chatContext($chat));
                if ($sourceAgent->inspect()?->status === \NeuronAI\Workflow\WorkflowStatus::Running) {
                    throw new \DomainException('原对话正在执行，请先停止或等待完成后再编辑');
                }
                $chat = $chat->editBeforeMessage($editMessageId, $editRevision, $question);
            } catch (\DomainException $e) {
                $this->error($e->getMessage());
            }
        }
        $tenantId = $this->tenantId();
        // 先建会话，让代码改动能挂到当前会话上。
        if (!$chat) {
            $chat = AgentChat::createForUser($tenantId, (int)$this->auth->id, $question);
        }
        $context = $this->chatContext($chat, $autoApprove, $control);
        if (str_contains((string)$this->request->header('accept'), 'text/event-stream')) {
            $this->streamReply($context, $chat, $question, $approval, $editing);
        }
        $startedAt = microtime(true);
        try {
            $reply = $this->runReply($context, $chat, $question, $approval);
            $durationMs = (int)round((microtime(true) - $startedAt) * 1000);
            $chat->recordReply($reply, $durationMs);
            (new AgentService())->acknowledge(new AdminAgent($context), $reply);
        } catch (AiException $e) {
            $this->error($e->getMessage());
        } catch (\DomainException $e) {
            $this->error($e->getMessage());
        } catch (\NeuronAI\Exceptions\HttpException | \NeuronAI\Exceptions\ProviderException $e) {
            Log::error('后台 Agent 模型接口异常：站点={tenant_id}，管理员={admin_id}，原因={message}', ['tenant_id' => $tenantId, 'admin_id' => (int)$this->auth->id, 'message' => $this->gatewayError($e)]);
            $this->error($this->gatewayError($e));
        } catch (\Throwable $e) {
            Log::error('后台 Agent 对话失败：站点={tenant_id}，管理员={admin_id}，异常={type}，位置={location}', ['tenant_id' => $tenantId, 'admin_id' => (int)$this->auth->id, 'type' => $e::class, 'location' => $e->getFile() . ':' . $e->getLine()]);
            $this->error('对话失败，请稍后重试');
        }
        $this->success('回复成功', '', $reply + ['id' => (int)$chat->id, 'duration_ms' => $durationMs,
            'messages' => $chat->transcript(), 'revision' => $chat->revision()]);
    }

    public function patches()
    {
        $chat = $this->ownedChat((int)$this->request->get('id/d', 0));
        $list = (new AgentPatchService())->listForChat((int)$chat->id, $this->tenantId(), (int)$this->auth->id);
        $this->success('获取成功', '', ['list' => $list]);
    }

    public function approve()
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $id = (int)$this->request->post('patch_id/d', 0);
        try {
            $result = (new AgentPatchService())->apply($id, $this->tenantId(), (int)$this->auth->id, 'manual');
        } catch (\DomainException $e) {
            $this->error($e->getMessage());
        }
        $this->success('已应用改动', '', $result);
    }

    public function reject()
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $id = (int)$this->request->post('patch_id/d', 0);
        try {
            $result = (new AgentPatchService())->reject($id, $this->tenantId(), (int)$this->auth->id);
        } catch (\DomainException $e) {
            $this->error($e->getMessage());
        }
        $this->success('已拒绝改动', '', $result);
    }

    public function rollback()
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $id = (int)$this->request->post('patch_id/d', 0);
        try {
            $result = (new AgentPatchService())->rollback($id, $this->tenantId(), (int)$this->auth->id);
        } catch (\DomainException $e) {
            $this->error($e->getMessage());
        }
        $this->success('已回退改动', '', $result);
    }

    public function delete()
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $chat = $this->ownedChat((int)$this->request->post('id/d', 0));
        $agent = new AdminAgent($this->chatContext($chat));
        $run = $agent->inspect();
        try {
            if ($run?->status === \NeuronAI\Workflow\WorkflowStatus::Completed) $agent->acknowledge($run->runId);
            elseif ($run !== null && !$agent->discardRun($run->runId, $run->executionAttempt)) throw new \DomainException('会话正在执行，请稍后删除');
        } catch (\Throwable $e) {
            $this->error('会话正在执行，请稍后删除');
        }
        (new AgentPatchService())->deleteForChat((int)$chat->id, $this->tenantId(), (int)$this->auth->id);
        foreach (AgentTask::listForChat($this->tenantId(), (int)$this->auth->id, (int)$chat->id) as $task) {
            if (in_array($task['status'], ['queued', 'running'], true)) {
                AgentTask::cancel($task['id'], $this->tenantId(), (int)$this->auth->id);
            }
        }
        foreach ($chat->conversationVersions() as $version) {
            $versionAgent = new AdminAgent(AgentContext::fromAuth($this->auth, (int)$chat->id, conversationVersion: $version));
            AgentStorage::clearThread($versionAgent->threadId());
        }
        $chat->delete();
        $this->success('已删除会话');
    }

    public function taskStatus()
    {
        if (!$this->auth->check('agent/index')) $this->error('无权查看任务进度');
        $chat = $this->ownedChat((int)$this->request->get('id/d', 0));
        $tasks = AgentTask::listForChat($this->tenantId(), (int)$this->auth->id, (int)$chat->id);
        $owners = [];
        foreach ($chat->transcript() as $message) {
            foreach ($message['task_ids'] ?? [] as $taskId) $owners[(int)$taskId] = $message['run_id'] ?? '';
        }
        foreach ($tasks as &$task) $task['run_id'] = $owners[$task['id']] ?? '';
        unset($task);
        $this->success('获取成功', '', ['tasks' => $tasks, 'fpm_worker' => AgentTaskService::usesFpm()]);
    }

    private function streamReply(AgentContext $context, AgentChat $chat, string $question, ?array $approval, bool $editing = false): never
    {
        // 直接输出 SSE 会跳过框架的收尾流程，先保存 ThinkPHP 会话并确保退出时日志落盘。
        $this->app->make('session')->save();
        register_shutdown_function(static function (): void { Log::save(); });
        if (function_exists('session_write_close')) {
            @session_write_close();
        }
        function_exists('set_time_limit') && @set_time_limit(180);
        @ini_set('zlib.output_compression', 'Off');
        while (ob_get_level() > 0 && @ob_end_clean()) {
        }

        header('Content-Type: text/event-stream; charset=utf-8');
        header('Cache-Control: no-cache, no-transform');
        header('X-Accel-Buffering: no');

        $emit = static function (string $event, array $data): void {
            echo 'event: ' . $event . "\n";
            echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
            flush();
        };

        $startedAt = microtime(true);
        $session = ['id' => (int)$chat->id, 'revision' => $chat->revision()];
        if ($editing) $session['messages'] = $chat->transcript();
        $emit('session', $session);
        $emit('progress', ['message' => '正在准备回答']);
        try {
            $reply = $this->runReply($context, $chat, $question, $approval, $emit);
            $durationMs = (int)round((microtime(true) - $startedAt) * 1000);
            $chat->recordReply($reply, $durationMs);
            (new AgentService())->acknowledge(new AdminAgent($context), $reply);
            $emit(($reply['stopped'] ?? false) ? 'stopped' : ($reply['approval'] !== null ? 'approval' : 'done'), $reply + [
                'id' => (int)$chat->id, 'duration_ms' => $durationMs, 'messages' => $chat->transcript(), 'revision' => $chat->revision()]);
        } catch (AiException $e) {
            $emit('error', ['message' => $e->getMessage()]);
        } catch (\DomainException $e) {
            $emit('error', ['message' => $e->getMessage()]);
        } catch (\NeuronAI\Exceptions\HttpException | \NeuronAI\Exceptions\ProviderException $e) {
            Log::error('后台 Agent 模型接口异常：站点={tenant_id}，管理员={admin_id}，原因={message}', ['tenant_id' => $context->tenantId, 'admin_id' => (int)$this->auth->id, 'message' => $this->gatewayError($e)]);
            $emit('error', ['message' => $this->gatewayError($e)]);
        } catch (\Throwable $e) {
            Log::error('后台 Agent 流式对话失败：站点={tenant_id}，管理员={admin_id}，异常={type}，位置={location}', ['tenant_id' => $context->tenantId, 'admin_id' => (int)$this->auth->id, 'type' => $e::class, 'location' => $e->getFile() . ':' . $e->getLine()]);
            $emit('error', ['message' => '对话失败，请稍后重试']);
        }
        exit;
    }

    private function runReply(AgentContext $context, AgentChat $chat, string $question, ?array $approval, ?callable $progress = null): array
    {
        $service = new AgentService();
        if ($approval === null) return $service->reply($context, $chat->transcript(), $question, $progress);
        return $service->resume(new AdminAgent($context, $progress), $approval['run_id'], $approval['attempt'], $approval['decision'], $progress);
    }

    private function ownedChat(int $id): AgentChat
    {
        $chat = $id > 0 ? AgentChat::findForUser($id, $this->tenantId(), (int)$this->auth->id) : null;
        if (!$chat) {
            $this->error('会话不存在或无权访问');
        }
        return $chat;
    }

    private function chatContext(AgentChat $chat, bool $autoApprove = false, ?AgentRunControl $control = null): AgentContext
    {
        return AgentContext::fromAuth($this->auth, (int)$chat->id, $autoApprove, $control, $chat->conversationVersion());
    }

    /** 官网积分或登录错误直接提示，其余只保留一段可读信息。 */
    private function gatewayError(\Throwable $e): string
    {
        $explained = AiGateway::explain($e);
        if ($explained instanceof AiException) {
            return $explained->getMessage();
        }
        $message = $explained->getMessage();
        if ($e instanceof \NeuronAI\Exceptions\HttpException && $e->response !== null) {
            $body = json_decode($e->response->body, true);
            $message = (string)($body['error']['message'] ?? $body['message'] ?? $message);
        }
        if (preg_match('/"message":"([^"]+)"/', $message, $matches)) {
            $message = $matches[1];
        }
        if (preg_match('/model.{0,40}busy|busy.{0,40}model|overloaded|temporarily unavailable|模型.{0,20}繁忙/iu', $message)) {
            return '当前所选 AI 模型上游繁忙，请稍后重试，或在“AI 管理 → 模型设置”切换其他可用模型。';
        }
        if ($message === '模型返回空响应') return '模型连续返回空响应，请稍后重试或在官网后台切换其他模型。';
        if ($message === '模型未完整返回工具参数') return '模型未完整返回工具调用参数，请重试或切换支持工具调用的模型。';
        if ($message === 'The stream ended before the answer was complete.') return '模型连接提前中断，回答尚未完成，请稍后重试。';
        return mb_substr($message, 0, 200);
    }

    private function tenantId(): int
    {
        return 0;
    }
}

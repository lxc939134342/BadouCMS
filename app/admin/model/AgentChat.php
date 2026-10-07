<?php

namespace app\admin\model;

use think\Model;
use think\facade\Db;

class AgentChat extends Model
{
    protected $name = 'agent_chat';
    protected $autoWriteTimestamp = 'int';

    public static function findForUser(int $id, int $tenantId, int $adminId, bool $lock = false): ?self
    {
        return static::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('admin_id', $adminId)
            ->lock($lock)
            ->find();
    }

    public static function listForUser(int $tenantId, int $adminId): array
    {
        return static::where('tenant_id', $tenantId)
            ->where('admin_id', $adminId)
            ->order('update_time', 'desc')
            ->limit(30)
            ->field('id,title,update_time')
            ->select()
            ->toArray();
    }

    public static function createForUser(int $tenantId, int $adminId, string $question): self
    {
        $chat = new self();
        $chat->save([
            'tenant_id' => $tenantId,
            'admin_id' => $adminId,
            'title' => mb_substr($question, 0, 60),
            'messages' => '[]',
        ]);
        return $chat;
    }

    public function transcript(): array
    {
        $state = $this->conversationState();
        $messages = $state['messages'];
        // 旧消息按会话和位置生成稳定标识，下一次保存时一并落库，截取最近轮次后也能准确定位。
        foreach ($messages as $index => &$message) {
            if (($message['role'] ?? '') === 'user' && empty($message['message_id'])) {
                $message['message_id'] = substr(hash('sha256', $this->id . ':' . $index . ':' . ($message['content'] ?? '')), 0, 32);
            }
        }
        unset($message);
        // 旧消息仅按明确提及的真实任务编号关联，不按工具出现次数猜测。
        $tasks = \app\common\agent\model\AgentTask::listForChat((int)$this->tenant_id, (int)$this->admin_id, (int)$this->id, 1000);
        $claimed = [];
        foreach ($messages as $message) {
            foreach ($message['task_ids'] ?? [] as $taskId) $claimed[(int)$taskId] = true;
        }
        $available = array_column($tasks, null, 'id');
        foreach ($messages as &$message) {
            if (($message['role'] ?? '') !== 'assistant' || array_key_exists('task_ids', $message)
                || !in_array('提交后台复制翻译任务', $message['tools'] ?? [], true)) continue;
            $message['task_ids'] = [];
            preg_match_all('/任务\s*(?:ID|编号)\s*[:：|]\s*(\d+)/iu', $message['content'] ?? '', $matches);
            foreach ($matches[1] as $taskId) {
                $taskId = (int)$taskId;
                if (isset($available[$taskId]) && !isset($claimed[$taskId])) {
                    $message['task_ids'][] = $taskId;
                    $claimed[$taskId] = true;
                }
            }
        }
        unset($message);
        return $messages;
    }

    public function revision(): string
    {
        return hash('sha256', (string)$this->getAttr('messages'));
    }

    /** 兼容旧会话的消息数组；编辑后的版本和历史仍存放在同一会话中。 */
    private function conversationState(): array
    {
        $stored = json_decode((string)$this->getAttr('messages'), true);
        if (!is_array($stored)) return ['version' => '', 'messages' => [], 'history' => []];
        if (array_is_list($stored)) return ['version' => '', 'messages' => $stored, 'history' => []];
        return ['version' => (string)($stored['version'] ?? ''),
            'messages' => (array)($stored['messages'] ?? []), 'history' => (array)($stored['history'] ?? [])];
    }

    public function conversationVersion(): string
    {
        return $this->conversationState()['version'];
    }

    /** 仅用于清理当前会话的各版本模型线程，不向会话列表新增记录。 */
    public function conversationVersions(): array
    {
        $state = $this->conversationState();
        return array_values(array_unique(array_merge([$state['version']], array_column($state['history'], 'version'))));
    }

    /** 回到编辑点之前，保留旧问答供内部追溯，并为新问答隔离模型上下文和审批。 */
    public function editBeforeMessage(string $messageId, string $revision, string $question): self
    {
        return Db::transaction(function () use ($messageId, $revision, $question): self {
            $chat = self::findForUser((int)$this->id, (int)$this->tenant_id, (int)$this->admin_id, true);
            if (!$chat || !hash_equals($chat->revision(), $revision)) throw new \DomainException('对话已更新，请刷新后重新选择要编辑的消息');
            $messages = $chat->transcript();
            $index = null;
            foreach ($messages as $key => $message) {
                if (($message['role'] ?? '') === 'user' && ($message['message_id'] ?? '') === $messageId) { $index = $key; break; }
            }
            if ($index === null) throw new \DomainException('要编辑的消息已不存在，请刷新对话');
            $state = $chat->conversationState();
            $state['history'][] = ['version' => $state['version'], 'title' => (string)$chat->title,
                'messages' => $messages, 'edited_message_id' => $messageId, 'saved_at' => time()];
            $state['version'] = bin2hex(random_bytes(16));
            $state['messages'] = array_slice($messages, 0, $index);
            foreach ($state['messages'] as &$message) $message['approval'] = null;
            unset($message);
            if (!$chat->save(['title' => mb_substr((string)($state['messages'][0]['content'] ?? $question), 0, 60),
                'messages' => json_encode($state, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)])) {
                throw new \DomainException('编辑消息保存失败，请重试');
            }
            return $chat;
        });
    }

    /** 防止旧请求在编辑后落库，覆盖新版本；历史归档不参与模型上下文。 */
    private function saveMessages(array $messages): void
    {
        $state = $this->conversationState();
        $stored = $state['version'] === '' ? $messages : array_replace($state, ['messages' => $messages]);
        $serialized = json_encode($stored, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        Db::transaction(function () use ($serialized): void {
            $fresh = self::findForUser((int)$this->id, (int)$this->tenant_id, (int)$this->admin_id, true);
            if (!$fresh || !hash_equals($fresh->revision(), $this->revision())) throw new \DomainException('对话已更新，请刷新后重试');
            if (!$fresh->save(['messages' => $serialized])) throw new \DomainException('对话保存失败，请重试');
            $this->setAttr('messages', $serialized);
            $this->setAttr('update_time', $fresh->update_time);
        });
    }

    /** 同一运行的审批和最终回答覆盖同一轮，恢复结果时也不会重复追加。 */
    public function recordReply(array $reply, int $durationMs): void
    {
        $messages = $this->transcript();
        $index = null;
        foreach ($messages as $key => $message) {
            if (($message['run_id'] ?? null) === $reply['run_id']) $index = $key;
        }
        if ($index === null) {
            $messages[] = ['role' => 'user', 'content' => $reply['question'], 'message_id' => bin2hex(random_bytes(16))];
            $index = count($messages);
        }
        $messages[$index] = [
            'role' => 'assistant', 'content' => $reply['answer'], 'run_id' => $reply['run_id'],
            'tools' => array_values(array_unique(array_merge($messages[$index]['tools'] ?? [], $reply['tools']))),
            'approval' => $reply['approval'], 'duration_ms' => $durationMs, 'stopped' => (bool)($reply['stopped'] ?? false),
            'task_ids' => array_values(array_unique(array_merge($messages[$index]['task_ids'] ?? [], $reply['task_ids'] ?? []))),
        ];
        $this->saveMessages(array_slice($messages, -40));
    }

    public function appendExchange(string $question, string $answer, array $tools, int $durationMs = 0): array
    {
        $messages = $this->transcript();
        $messages[] = ['role' => 'user', 'content' => $question, 'message_id' => bin2hex(random_bytes(16))];
        $messages[] = ['role' => 'assistant', 'content' => $answer, 'tools' => $tools, 'duration_ms' => $durationMs];
        // 只保存最近 20 轮，控制请求上下文和单行数据大小。
        $messages = array_slice($messages, -40);
        $this->saveMessages($messages);
        return $messages;
    }
}

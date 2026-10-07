<?php

namespace app\common\agent;

use think\facade\Cache;

/** 独立请求标识允许在新会话尚未返回 ID 时停止；标识始终绑定后台身份。 */
class AgentRunControl
{
    private string $key;
    private float $lastCheck = 0;
    private bool $stopped = false;

    public function __construct(int $tenantId, int $adminId, public readonly string $requestId)
    {
        if ($adminId <= 0 || !preg_match('/^[a-f0-9]{32}$/D', $requestId)) throw new \DomainException('Agent 请求标识无效');
        $this->key = 'agent_stop_' . hash('sha256', $tenantId . ':' . $adminId . ':' . $requestId);
    }

    public function stop(): void
    {
        if (!Cache::set($this->key, true, 600)) throw new \DomainException('停止请求保存失败，请重试');
    }

    public function check(bool $force = false): void
    {
        // cURL 中断后 Provider 会立即再次检查，已确认的停止不能被检查间隔跳过。
        if ($this->stopped) throw new AgentStoppedException();
        $now = microtime(true);
        if (!$force && $now - $this->lastCheck < 0.2) return;
        $this->lastCheck = $now;
        if (Cache::get($this->key, false)) {
            $this->stopped = true;
            throw new AgentStoppedException();
        }
    }
}

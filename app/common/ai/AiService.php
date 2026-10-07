<?php

namespace app\common\ai;

use app\common\agent\AgentContext;
use app\common\agent\BaseAgent;
use NeuronAI\Chat\Messages\UserMessage;

/** 插件的一次性生成入口，默认不携带任何后台操作工具。 */
class AiService
{
    public static function text(AgentContext $context, string $permission, string $prompt): string
    {
        $answer = trim((string)self::agent($context, $permission)->chat(new UserMessage($prompt))->getMessage()?->getContent());
        if ($answer === '') throw new \DomainException('模型没有返回文字，请稍后重试');
        return $answer;
    }

    public static function structured(AgentContext $context, string $permission, string $prompt, string $class): mixed
    {
        return self::agent($context, $permission)->structured(new UserMessage($prompt), $class);
    }

    private static function agent(AgentContext $context, string $permission): BaseAgent
    {
        if ($context->adminId <= 0 || $permission === '' || !$context->auth->check($permission, $context->adminId)) {
            throw new \DomainException('当前后台账号没有使用该 AI 功能的权限');
        }
        // 一次性生成不与聊天线程共享历史，也不继承自动批准设置。
        return new BaseAgent(new AgentContext($context->auth, $context->adminId, $context->tenantId, control: $context->control));
    }
}

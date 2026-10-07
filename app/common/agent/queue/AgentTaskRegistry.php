<?php

namespace app\common\agent\queue;

/**
 * Agent 任务注册中心
 * 管理所有任务类型与处理器的映射关系
 */
class AgentTaskRegistry
{
    /**
     * 已注册的任务处理器
     * @var array<string, string>
     */
    private static array $handlers = [];

    /**
     * 注册任务处理器
     * 
     * @param string $type 任务类型，如：cms.translate, order.export
     * @param string $handler 处理器类名，必须实现 AgentTaskInterface
     * @throws \InvalidArgumentException
     */
    public static function register(string $type, string $handler): void
    {
        if (!is_subclass_of($handler, AgentTaskInterface::class)) {
            throw new \InvalidArgumentException("{$handler} 必须实现 AgentTaskInterface 接口");
        }
        
        self::$handlers[$type] = $handler;
    }

    /**
     * 获取任务处理器类名
     * 
     * @param string $type 任务类型
     * @return string|null 处理器类名，不存在返回 null
     */
    public static function get(string $type): ?string
    {
        return self::$handlers[$type] ?? null;
    }

    /**
     * 获取所有已注册的任务类型
     * 
     * @return array<string, string>
     */
    public static function all(): array
    {
        return self::$handlers;
    }

    /**
     * 检查任务类型是否已注册
     */
    public static function has(string $type): bool
    {
        return isset(self::$handlers[$type]);
    }

    /**
     * 创建任务处理器实例
     */
    public static function make(string $type): ?AgentTaskInterface
    {
        // CLI 消费者没有经过工具注册流程，按任务所属模块加载配置。
        if (!self::has($type)) {
            $module = explode('.', $type)[0];
            if (preg_match('/^[a-zA-Z0-9_-]+$/D', $module)) {
                $file = \badou\Server::getModuleDir($module) . 'agent/config.php';
                // 配置内可能使用同名局部变量，隔离 require 的变量作用域。
                if (is_file($file)) (static fn (string $configFile) => require $configFile)($file);
            }
        }
        $handler = self::get($type);
        if (!$handler) {
            return null;
        }
        
        return new $handler();
    }
}

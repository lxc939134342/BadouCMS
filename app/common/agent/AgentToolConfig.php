<?php

namespace app\common\agent;

/** 配置数组复用公共注册流程，插件开发者无需编写 Provider 类。 */
class AgentToolConfig extends AbstractAgentToolProvider
{
    private array $tools;
    private array $scenes;
    private array $residentTools;
    private string $guidelines;

    public function __construct(array $config, ?self $parent = null)
    {
        $config += ['tools' => [], 'guidelines' => '', 'scenes' => $parent?->scenes ?? ['admin'],
            'resident_tools' => $parent?->residentTools() ?? []];
        if (array_diff(array_keys($config), ['tools', 'guidelines', 'scenes', 'groups', 'resident_tools']) !== []
            || !is_array($config['tools']) || !is_string($config['guidelines']) || !is_array($config['scenes'])
            || array_filter($config['scenes'], 'is_string') !== $config['scenes']
            || !is_array($config['resident_tools'])
            || array_filter($config['resident_tools'], static fn ($name) => is_string($name)
                && preg_match('/^[a-zA-Z0-9_-]{1,64}$/D', $name)) !== $config['resident_tools']) {
            throw new \LogicException('Agent 配置字段无效');
        }
        $this->tools = $config['tools'];
        $this->scenes = $config['scenes'];
        $this->residentTools = array_values(array_unique($config['resident_tools']));
        $this->guidelines = trim(($parent?->guidelines() ?? '') . "\n" . $config['guidelines']);
    }

    public function agentTools(): array
    {
        return array_map(fn ($tool) => is_array($tool) ? $tool + ['scenes' => $this->scenes] : $tool, $this->tools);
    }

    public function guidelines(): string { return $this->guidelines; }
    public function residentTools(): array { return $this->residentTools; }
}

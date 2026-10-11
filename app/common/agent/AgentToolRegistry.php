<?php

namespace app\common\agent;

use badou\Server;
use think\facade\Log;

/** 兼容旧数组/接口，统一补齐元数据并按场景、模块、权限过滤。 */
class AgentToolRegistry
{
    public function available(AgentContext $context, AgentToolExecutor $executor, string $scene = 'admin', array $modules = ['*']): array
    {
        return array_values($this->gather($context, $executor, $scene, $modules)[0]);
    }

    /** 首页能力卡片：只保留依赖工具全部可用的示例，没有示例的卡片不展示。 */
    public function showcases(AgentContext $context, AgentToolExecutor $executor, string $scene = 'admin', array $modules = ['*']): array
    {
        [$tools, $cards] = $this->gather($context, $executor, $scene, $modules, true);
        $names = array_keys($tools);
        $result = [];
        foreach ($cards as $card) {
            $prompts = [];
            foreach ($card['prompts'] as $prompt) {
                if (array_diff($prompt['tools'], $names) === []) $prompts[] = $prompt['text'];
            }
            if ($prompts === []) continue;
            $result[] = ['module' => $card['module'], 'title' => $card['title'], 'icon' => $card['icon'],
                'description' => $card['description'], 'prompts' => $prompts];
        }
        return $result;
    }

    /** @return array{0: array<string, array>, 1: array<int, array>} */
    private function gather(AgentContext $context, AgentToolExecutor $executor, string $scene, array $modules, bool $withShowcases = false): array
    {
        if ($modules === []) return [[], []];
        $available = [];
        $cards = [];
        if (in_array('*', $modules, true) || in_array('core', $modules, true)) {
            [$available, $cards] = $this->collect($this->coreProviders(), 'core', $scene, $context, $executor, $withShowcases);
        }
        foreach ($this->modules() as $module) {
            $name = (string)$module['name'];
            if ((int)($module['state'] ?? 0) !== 1 || (!in_array('*', $modules, true) && !in_array($name, $modules, true))) continue;
            try {
                [$tools, $moduleCards] = $this->collect($this->moduleProviders($name), $name, $scene, $context, $executor, $withShowcases);
                if (array_intersect_key($available, $tools) !== []) throw new \LogicException('Agent 工具名称重复');
                $available += $tools;
                $cards = array_merge($cards, $moduleCards);
            } catch (\Throwable $e) {
                // 一个插件注册失败只隔离该插件，不影响其他插件和后台助手。
                Log::error('Agent 插件工具注册失败：模块={module}，异常={type}', ['module' => $name, 'type' => $e::class]);
            }
        }
        return [$available, $cards];
    }

    protected function modules(): array { return Server::getInstalldModuleList(); }

    protected function moduleConfigFile(string $name): string
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $name)) throw new \LogicException('Agent 插件名称无效');
        $directory = Server::getModuleDir($name);
        $config = $directory . 'agent/config.php';
        // 优先使用 Agent 目录内的配置，兼容旧插件根目录入口。
        return is_file($config) ? $config : $directory . 'agent.php';
    }

    protected function moduleProviders(string $name): array
    {
        $file = $this->moduleConfigFile($name);
        if (!is_file($file)) {
            $provider = $this->moduleProvider($name);
            return $provider === null ? [] : [$provider];
        }
        // 配置文件是唯一入口；没有配置的旧插件继续使用主类注册，避免重复加载同一工具。
        $config = require $file;
        if (!is_array($config) || !is_array($config['groups'] ?? [])) throw new \LogicException('Agent 配置必须返回数组');
        $root = new AgentToolConfig($config);
        $providers = [$root];
        foreach ($config['groups'] ?? [] as $group) {
            if (!is_array($group) || array_key_exists('groups', $group)) throw new \LogicException('Agent 工具组配置无效');
            $providers[] = new AgentToolConfig($group, $root);
        }
        return $providers;
    }

    protected function moduleProvider(string $name): ?AgentToolProviderInterface
    {
        $class = Server::getModuleClass($name);
        return $class !== '' && is_subclass_of($class, AgentToolProviderInterface::class) ? app()->make($class) : null;
    }

    protected function coreProviders(): array
    {
        $files = array_merge(glob(__DIR__ . '/tools/*Tool.php'), glob(__DIR__ . '/tools/*ToolProvider.php'));
        sort($files, SORT_STRING);
        return array_map(fn ($file) => app()->make(__NAMESPACE__ . '\\tools\\' . basename($file, '.php')), $files);
    }

    private function collect(array $providers, string $module, string $scene, AgentContext $context, AgentToolExecutor $executor, bool $withShowcases): array
    {
        $tools = [];
        $cards = [];
        $names = [];
        foreach ($providers as $provider) {
            $definitions = $provider instanceof AgentToolProviderInterface ? $provider->agentTools() : [$provider];
            $guidelines = $provider instanceof AbstractAgentToolProvider ? $provider->guidelines() : '';
            $residentTools = $provider instanceof AbstractAgentToolProvider ? $provider->residentTools() : [];
            if ($withShowcases && $provider instanceof AbstractAgentToolProvider) {
                // 展示配置独立隔离，卡片异常不能导致该组工具失效。
                try {
                    $showcase = $provider->showcase();
                    if ($showcase !== []) {
                        $card = $this->showcaseCard($showcase, $module);
                        if ($card !== null) $cards[] = $card;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Agent 能力卡片加载失败：模块={module}，异常={type}', ['module' => $module, 'type' => $e::class]);
                }
            }
            foreach ($definitions as $tool) {
                if ($tool instanceof AbstractAgentTool) $tool = $tool->definition();
                elseif ($tool instanceof AgentToolInterface) $tool = [
                    'name' => $tool->name(), 'description' => $tool->description(), 'permission' => $tool->permission(),
                    'properties' => $tool->properties(), 'handler' => [$tool, 'execute'],
                ];
                if (!is_array($tool) || !is_string($tool['name'] ?? null)
                    || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/D', $tool['name'])
                    || !is_string($tool['description'] ?? null) || !is_string($tool['permission'] ?? null)
                    || $tool['permission'] === '' || !is_array($tool['properties'] ?? null)
                    || !is_callable($tool['handler'] ?? null)) throw new \LogicException('Agent 工具定义无效');
                if (isset($names[$tool['name']])) throw new \LogicException('Agent 工具名称重复：' . $tool['name']);
                $names[$tool['name']] = true;
                $tool += ['label' => $tool['name'], 'scenes' => ['admin'], 'approval' => false, 'guidelines' => $guidelines,
                    'resident' => in_array($tool['name'], $residentTools, true)];
                if (!is_string($tool['label']) || !is_string($tool['guidelines']) || !is_array($tool['scenes'])
                    || !is_bool($tool['resident']) || (!is_bool($tool['approval']) && !is_string($tool['approval']))) throw new \LogicException('Agent 工具元数据无效');
                if (isset($tool['approval_display']) && !is_callable($tool['approval_display'])) throw new \LogicException('Agent 审批展示配置无效');
                if (isset($tool['approval_validate']) && !is_callable($tool['approval_validate'])) throw new \LogicException('Agent 审批预检配置无效');
                if (isset($tool['allow_auto_approve']) && !is_bool($tool['allow_auto_approve'])) throw new \LogicException('Agent 自动批准配置无效');
                $tool['module'] = $module;
                // 在插件隔离边界内校验参数 schema，避免延迟到 Agent 启动才失败。
                (new NeuronToolAdapter($executor))->adapt($tool, $context);
                if (in_array($scene, $tool['scenes'], true) && $executor->allowed($tool, $context)) $tools[$tool['name']] = $tool;
            }
        }
        return [$tools, $cards];
    }

    /** 展示信息不影响工具注册：格式错误只丢弃该卡片并记录日志。 */
    private function showcaseCard(array $showcase, string $module): ?array
    {
        $title = $showcase['title'] ?? null;
        $icon = $showcase['icon'] ?? 'fa-cube';
        $description = $showcase['description'] ?? '';
        $prompts = $showcase['prompts'] ?? null;
        $valid = is_string($title) && trim($title) !== '' && mb_strlen($title) <= 20
            && is_string($icon) && preg_match('/^fa-[a-z0-9-]{1,40}$/D', $icon)
            && is_string($description) && mb_strlen($description) <= 80
            && is_array($prompts) && $prompts !== [] && count($prompts) <= 4;
        $items = [];
        foreach ($valid ? $prompts : [] as $prompt) {
            $text = is_array($prompt) ? ($prompt['text'] ?? null) : null;
            $needs = is_array($prompt) ? ($prompt['tools'] ?? null) : null;
            if (!is_string($text) || trim($text) === '' || mb_strlen($text) > 120 || !is_array($needs) || $needs === []
                || array_filter($needs, static fn ($name) => is_string($name)
                    && preg_match('/^[a-zA-Z0-9_-]{1,64}$/D', $name)) !== $needs) {
                $valid = false;
                break;
            }
            $items[] = ['text' => trim($text), 'tools' => array_values($needs)];
        }
        if (!$valid) {
            Log::warning('Agent 能力卡片配置无效：模块={module}', ['module' => $module]);
            return null;
        }
        return ['module' => $module, 'title' => $title, 'icon' => $icon, 'description' => $description, 'prompts' => $items];
    }
}

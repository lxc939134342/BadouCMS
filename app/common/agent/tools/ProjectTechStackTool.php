<?php

namespace app\common\agent\tools;

use app\common\agent\AgentContext;
use app\common\agent\AbstractAgentTool;

class ProjectTechStackTool extends AbstractAgentTool
{
    protected string $label = '查看项目技术栈';

    public function name(): string { return 'project_tech_stack'; }

    public function description(): string { return '查询当前后台项目的 PHP 版本、Composer 主要依赖与后台前端技术。'; }

    public function permission(): string { return 'agent/index'; }

    public function properties(): array { return []; }

    public function execute(AgentContext $context, array $input): array
    {
        $path = root_path() . 'composer.json';
        $manifest = is_file($path) ? json_decode((string)file_get_contents($path), true) : null;
        if (!is_array($manifest)) {
            throw new \DomainException('无法读取项目依赖清单');
        }
        $required = $manifest['require'] ?? [];
        $packages = [];
        foreach (['topthink/framework', 'topthink/think-orm', 'topthink/think-multi-app', 'neuron-core/neuron-ai', 'workerman/workerman'] as $package) {
            if (isset($required[$package])) {
                $packages[$package] = $required[$package];
            }
        }
        return [
            'project' => $manifest['name'] ?? 'badoucms',
            'php_version' => PHP_VERSION,
            'php_requirement' => $required['php'] ?? '',
            'composer_requirements' => $packages,
            'admin_frontend' => ['Layui', 'Pear Admin', 'BadouAdmin', 'jQuery'],
        ];
    }
}

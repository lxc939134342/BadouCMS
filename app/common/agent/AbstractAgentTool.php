<?php

namespace app\common\agent;

/** 插件工具只需声明参数和权限，并在 execute() 中调用已有业务。 */
abstract class AbstractAgentTool implements AgentToolInterface
{
    protected string $name;
    protected string $label = '';
    protected string $description = '';
    protected string $permission;
    protected array $scenes = ['admin'];
    protected bool|string $approval = false;

    public function name(): string { return $this->name; }
    public function description(): string { return $this->description; }
    public function permission(): string { return $this->permission; }
    public function properties(): array { return []; }

    public function definition(): array
    {
        return [
            'name' => $this->name(),
            'label' => $this->label ?: $this->name(),
            'description' => $this->description(),
            'permission' => $this->permission(),
            'properties' => $this->properties(),
            'scenes' => $this->scenes,
            'approval' => $this->approval,
            'handler' => [$this, 'execute'],
        ];
    }
}

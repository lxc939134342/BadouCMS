<?php

namespace app\common\agent;

use NeuronAI\Tools\Tool;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Tools\ToolPropertyInterface;

class NeuronToolAdapter
{
    public function __construct(private readonly AgentToolExecutor $executor)
    {
    }

    public function adapt(array $definition, AgentContext $context, ?callable $onCall = null): Tool
    {
        $properties = [];
        foreach ($definition['properties'] as $property) {
            if (is_array($property) && isset($property['name'], $property['type'])) {
                $property = ToolProperty::make(
                    $property['name'],
                    PropertyType::from($property['type']),
                    $property['description'] ?? '',
                    $property['required'] ?? false,
                    $property['enum'] ?? [],
                    $property['nullable'] ?? false,
                );
            }
            if (!$property instanceof ToolPropertyInterface) {
                throw new \LogicException('Agent 工具参数配置无效');
            }
            $properties[] = $property;
        }

        return new class($this->executor, $definition, $context, $onCall, $properties) extends Tool {
            protected string $name;

            protected ?string $description;

            public function __construct(
                private readonly AgentToolExecutor $executor,
                private readonly array $definition,
                private readonly AgentContext $context,
                private readonly mixed $onCall,
                private readonly array $toolProperties,
            ) {
                $this->name = $definition['name'];
                $this->description = $definition['description'];
            }

            protected function properties(): array
            {
                return $this->toolProperties;
            }

            protected function approvalPolicy(): bool|string
            {
                $approval = $this->definition['approval'] ?? false;
                try { $this->executor->validateApproval($this->definition, $this->context, $this->getInputs(), $this->getCallId(), true); }
                catch (\DomainException $e) {
                    // 无效输入不交给用户批准；执行层再次校验并把错误交回模型纠正。
                    return false;
                }
                // 只允许工具显式声明的后台批量任务响应用户选择的自动批准，其他写入仍维持原规则。
                if (($this->definition['allow_auto_approve'] ?? false) === true && $this->context->autoApprove) return false;
                // 旧工具 confirm=false 保留预览；true 仅表示模型提出执行请求。
                if ($approval === 'confirm') return ($this->getInputs()['confirm'] ?? false) === true;
                return $approval;
            }

            public function __invoke(...$input): string
            {
                $definition = $this->definition;
                $context = $this->context;
                $onCall = $this->onCall;
                $onCall && $onCall('start', $definition['name']);
                try {
                    $result = $this->executor->execute($definition, $context, $input, $this->getCallId());
                } catch (\Throwable $e) {
                    $onCall && $onCall('error', $definition['name'], $e->getMessage());
                    throw $e;
                }
                $onCall && $onCall('done', $definition['name']);
                return json_encode(
                    $result,
                    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                );
            }
        };
    }
}

<?php

namespace app\common\agent\middleware;

/** 保留 Neuron 的检索排序，补充中文分词并避免把 CMS 拆成 c/m/s。 */
class ToolSearchTool extends \NeuronAI\Agent\Middleware\ToolSearchTool
{
    protected ?string $description = '查找可用工具。使用简短关键词或完整工具名；找到后直接调用，不要重复搜索。';

    public function __construct(array $toolPool, int $topN = 8)
    {
        parent::__construct($toolPool, $topN);
        $this->setMaxRuns(3);
    }

    protected function properties(): array
    {
        return [\NeuronAI\Tools\ToolProperty::make(
            name: 'query', type: \NeuronAI\Tools\PropertyType::STRING,
            description: '必填检索词，例如 CMS 文章、会员或完整工具名；调用格式：{"query":"CMS 文章"}。', required: true,
        )];
    }

    protected function tokenize(string $text): array
    {
        $text = mb_strtolower(preg_replace('/(?<=[a-z0-9])(?=[A-Z])/', '_', $text));
        $parts = preg_split('/[\s\p{P}]+|(?<=\p{Han})(?=[a-z0-9])|(?<=[a-z0-9])(?=\p{Han})/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $words = $parts;
        foreach ($parts as $part) {
            if (!preg_match('/^\p{Han}{3,}$/u', $part)) continue;
            for ($i = 0; $i < mb_strlen($part) - 1; $i++) $words[] = mb_substr($part, $i, 2);
        }
        return array_values(array_unique($words));
    }

    public function search(string $query): array
    {
        // 完整工具名只加载目标工具。
        foreach ($this->toolPool as $tool) if (strcasecmp(trim($query), $tool->getName()) === 0) return [$tool];
        return parent::search($query);
    }

    public function __invoke(string $query): string
    {
        $matches = $this->search($query);
        \think\facade\Log::info('Agent 工具检索结果：工具={tools}', [
            'tools' => implode(',', array_map(fn ($tool) => $tool->getName(), $matches)) ?: '无匹配',
        ]);
        if ($matches !== []) {
            $lines = array_map(fn ($tool) => $tool->getName() . ': ' . $tool->getDescription(), $matches);
            return "已找到工具：\n" . implode("\n", $lines) . "\n本次仅检索工具，尚未执行业务操作。请根据工具参数重新调用找到的工具，不要重复查找。";
        }
        $names = array_map(fn ($tool) => $tool->getName(), $this->toolPool);
        return "没有匹配工具；可用工具名：" . implode('、', $names)
            . "。可以改用完整工具名查询；若仍没有所需功能，请明确说明，不要反复搜索。";
    }
}

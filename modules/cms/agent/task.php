<?php

use app\common\agent\AgentContext;
use app\common\agent\queue\{AgentTaskRegistry, AgentTaskService};
use app\common\agent\model\AgentTask;
use modules\cms\agent\task\LanguageTranslateTask;

// 注册 CMS 语言翻译任务处理器
AgentTaskRegistry::register('cms.translate', LanguageTranslateTask::class);

$properties = [
    ['name' => 'source', 'type' => 'string', 'description' => '源语言编码', 'required' => true],
    ['name' => 'target', 'type' => 'string', 'description' => '目标语言编码', 'required' => true],
    ['name' => 'kind', 'type' => 'string', 'enum' => ['categories', 'contents', 'both'], 'description' => 'categories 只翻译栏目；contents 翻译文章和单页；both 翻译栏目及内容，默认 categories'],
    ['name' => 'source_scode', 'type' => 'string', 'description' => '可选：只处理此源栏目及子栏目；省略或留空处理全部'],
    ['name' => 'copy_first', 'type' => 'boolean', 'description' => '用户要求先复制再翻译时填 true；已有目标只要求翻译时填 false，默认 false'],
    ['name' => 'include_settings', 'type' => 'boolean', 'description' => '先复制时是否补齐缺失站点和公司配置，默认 false；仅明确要求才开启，配置不自动翻译'],
    ['name' => 'repair_mixed', 'type' => 'boolean', 'description' => '用户要求补译英文目标中残留的中文时填 true，保留已有英文部分；默认 false 保护与原文不同的内容。仅对 en / en-* 目标语言生效'],
];

return [
    // 可选：后台助手首页的能力卡片；prompts 中列出的工具都可用时才展示该示例。
    'showcase' => [
        'title' => '多语言站点', 'icon' => 'fa-language', 'description' => '复制内容到其他语言，并在后台整批翻译',
        'prompts' => [
            ['text' => '把中文站点的栏目复制到英文站并翻译', 'tools' => ['cms_language_list', 'cms_language_task_preview', 'cms_language_task_start']],
        ],
    ],
    'guidelines' => '批量复制/翻译默认走后台整项任务：先查语言，再用 cms_language_task_preview 获取整项范围与 snapshot，使用完全相同的参数和 snapshot 调用 cms_language_task_start；不要将后台任务拆成逐批人工审批。用户只要求栏目时 kind=categories；明确要求内容时 kind=contents；栏目及内容一起翻译用 both。要求复制后翻译时 copy_first=true，一次审批包含复制和后续翻译。任务返回 submitted=true 只代表提交，不能说已经全部完成；页面会自动更新进度，也可用 cms_language_task_status 查询。completed 才能报告任务执行结束，按 saved/skipped 说明真实结果；failed/cancelled/interrupted 必须说明尚未完成。译文保存成功后已实际写入草稿，没有另一轮"后台审批"。仅用户明确要求逐条审核译文时使用旧预览/保存工具。不得把生成好的译文当作保存成功，也不得把聊天回答中的表格当作任务进度。',
    'tools' => [
        [
            'name' => 'cms_language_task_preview',
            'label' => '预览整项复制翻译任务',
            'description' => '预览整个复制及翻译范围，固定源记录范围，返回记录数、语言名称和 snapshot；不写入数据，不需要按批审批。',
            'permission' => 'cms.content_sort/edit',
            'properties' => $properties,
            'handler' => static function (AgentContext $context, array $input): array {
                $handler = new LanguageTranslateTask();
                return $handler->preview($context, $input);
            },
        ],
        [
            'name' => 'cms_language_task_start',
            'label' => '提交后台复制翻译任务',
            'description' => '一次审批提交整个任务，后台自动复制、分批翻译并保存，无需后续逐批确认。关闭页面保留进度，再次打开自动接续；配置 CLI 执行器时持续后台处理。必须先获取整项任务预览，并传相同参数和 snapshot。提交不代表已经完成。',
            'permission' => 'cms.content_sort/edit',
            'approval' => true,
            'allow_auto_approve' => true,
            'properties' => array_merge($properties, [
                ['name' => 'snapshot', 'type' => 'string', 'description' => '整项任务预览返回的 snapshot', 'required' => true],
            ]),
            'approval_display' => static function (AgentContext $context, array $input): array {
                $handler = new LanguageTranslateTask();
                return $handler->approvalDisplay($context, $input);
            },
            'handler' => static function (AgentContext $context, array $input): array {
                $handler = new LanguageTranslateTask();
                $plan = $handler->preview($context, $input);

                // 验证 snapshot
                if (!isset($input['snapshot']) || !hash_equals($plan['snapshot'], $input['snapshot'])) {
                    throw new \DomainException('任务范围或数据已变化，请重新预览');
                }

                // 提交到通用任务队列
                $service = new AgentTaskService();
                $task = $service->submit('cms.translate', $context, ['plan' => $plan], priority: 5);

                return [
                    'submitted' => true,
                    'task' => $task->getPublicData(),
                    'message' => '已提交后台任务，后续自动分批保存，不需要再次审批。提交任务不代表翻译已完成，请以任务进度为准。',
                ];
            },
        ],
        [
            'name' => 'cms_language_task_status',
            'label' => '查看后台任务进度',
            'description' => '查询当前账号的后台复制翻译任务状态及实际保存/跳过数量；progress.skipped_items 包含最多 100 条跳过记录的源/目标 ID、标题和中文原因，skipped_items_truncated 表示明细被截断，不能猜测未返回的记录。旧任务没有明细时明确说明旧数据缺失。仅在拿到真实 task.id 后调用，不猜测任务 ID。只在 completed 时报告完成。',
            'permission' => 'cms.content_sort/index',
            'properties' => [
                ['name' => 'id', 'type' => 'integer', 'description' => '提交任务返回的 task.id', 'required' => true],
            ],
            'handler' => static function (AgentContext $context, array $input): array {
                $id = (int)($input['id'] ?? 0);
                if ($id <= 0) {
                    throw new \DomainException('任务 ID 无效');
                }

                $task = AgentTask::find($id);

                if (!$task || (int)$task->tenant_id !== $context->tenantId || (int)$task->admin_id !== $context->adminId) {
                    throw new \DomainException('任务不存在或无权访问');
                }

                return $task->getPublicData();
            },
        ],
    ],
];

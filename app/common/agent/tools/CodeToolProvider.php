<?php

namespace app\common\agent\tools;

use app\common\agent\AgentContext;
use app\common\agent\AbstractAgentToolProvider;
use app\common\agent\services\AgentPatchService;

class CodeToolProvider extends AbstractAgentToolProvider
{
    public function guidelines(): string
    {
        return '遇到用户粘贴的报错：先用 log_tail 看最新日志；需要定位文件时用 source_find 按文件名或路径搜索（不要凭记忆猜路径），再用 source_read 读源码定位原因；需要改代码时用 source_edit 生成改动（old_string 必须与源码逐字一致且在文件中唯一，改前务必先 source_read 确认），并提示用户在改动卡片上点“批准”应用；只有用户开启“自动批准”时才会先自审再自动应用。用户反馈改错了就用 source_rollback 回退。可修改范围由 config/agent.php 配置，不确定先用 source_policy 检查。代码读写仅超级管理员可用。';
    }

    public function agentTools(): array
    {
        $patches = new AgentPatchService();
        // 代码读写只对超级管理员开放，且路径已限制在项目源码目录内。
        $assertSuperAdmin = static function (AgentContext $context): void {
            if (!$context->auth->isSuperAdmin()) {
                throw new \DomainException('代码读写只允许超级管理员执行');
            }
        };

        return [
            [
                'name' => 'log_tail',
                'label' => '查看错误日志',
                'description' => '读取指定应用最近一个日志文件的最新若干行，用于排查报错。app 可为 admin、api、index 等，默认 admin。',
                'permission' => 'auth.rule/index',
                'properties' => [
                    ['name' => 'app', 'type' => 'string', 'description' => '应用名（runtime 下的目录名），默认 admin'],
                    ['name' => 'lines', 'type' => 'integer', 'description' => '读取行数，默认 200，最多 500'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($patches, $assertSuperAdmin): array {
                    $assertSuperAdmin($context);
                    $app = is_string($input['app'] ?? null) ? $input['app'] : 'admin';
                    $lines = (int)filter_var($input['lines'] ?? 200, FILTER_VALIDATE_INT);
                    return $patches->logTail($app, $lines ?: 200);
                },
            ],
            [
                'name' => 'source_read',
                'label' => '读取源码',
                'description' => '读取项目内某个源码文件的内容（带行号），用于定位报错位置。只能读项目源码目录，禁止 vendor、runtime、.env。',
                'permission' => 'auth.rule/index',
                'properties' => [
                    ['name' => 'file', 'type' => 'string', 'description' => '相对项目根目录的文件路径，例如 app/admin/controller/geo/AgentDemo.php', 'required' => true],
                    ['name' => 'offset', 'type' => 'integer', 'description' => '起始行号，从 1 开始，默认 1'],
                    ['name' => 'limit', 'type' => 'integer', 'description' => '读取行数，默认 400，最多 800'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($patches, $assertSuperAdmin): array {
                    $assertSuperAdmin($context);
                    $file = is_string($input['file'] ?? null) ? $input['file'] : '';
                    if ($file === '') {
                        throw new \DomainException('缺少 file');
                    }
                    $offset = (int)filter_var($input['offset'] ?? 1, FILTER_VALIDATE_INT);
                    $limit = (int)filter_var($input['limit'] ?? 400, FILTER_VALIDATE_INT);
                    return $patches->readFile($file, $offset ?: 1, $limit ?: 400);
                },
            ],
            [
                'name' => 'source_find',
                'label' => '搜索源码文件',
                'description' => '按文件名或路径片段搜索项目内的源码文件，返回相对项目根目录的路径。定位文件先用它，不要凭记忆猜路径。',
                'permission' => 'auth.rule/index',
                'properties' => [
                    ['name' => 'keyword', 'type' => 'string', 'description' => '文件名或路径关键词，例如 index.html、AgentDemo、agent_demo', 'required' => true],
                    ['name' => 'limit', 'type' => 'integer', 'description' => '最多返回数量，默认 50，最多 100'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($patches, $assertSuperAdmin): array {
                    $assertSuperAdmin($context);
                    $keyword = is_string($input['keyword'] ?? null) ? $input['keyword'] : '';
                    $limit = (int)filter_var($input['limit'] ?? 50, FILTER_VALIDATE_INT);
                    return $patches->findFiles($keyword, $limit ?: 50);
                },
            ],
            [
                'name' => 'source_policy',
                'label' => '查看可修改规则',
                'description' => '查看 Agent 可修改文件的规则（允许的后缀、禁止的目录/文件），或检查某个文件能否被修改。规则在 config/agent.php 里配置。',
                'permission' => 'auth.rule/index',
                'properties' => [
                    ['name' => 'file', 'type' => 'string', 'description' => '可选：要检查的文件路径；留空则只返回规则'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($patches, $assertSuperAdmin): array {
                    $assertSuperAdmin($context);
                    $result = ['policy' => $patches->policy()];
                    $file = is_string($input['file'] ?? null) ? trim($input['file']) : '';
                    if ($file !== '') {
                        $result['check'] = $patches->checkFile($file);
                    }
                    return $result;
                },
            ],
            [
                'name' => 'source_edit',
                'label' => '提出代码改动',
                'description' => '提出一次源码改动（不直接写盘）：用 old_string 精确匹配唯一片段并替换为 new_string，返回 diff 供用户批准。auto_approve 开启时会先让 AI 自审再决定是否自动应用。改前务必先用 source_read 确认原文。',
                'permission' => 'auth.rule/edit',
                'properties' => [
                    ['name' => 'file', 'type' => 'string', 'description' => '相对项目根目录的文件路径', 'required' => true],
                    ['name' => 'old_string', 'type' => 'string', 'description' => '要被替换的原文，必须与文件内容完全一致且在文件中唯一', 'required' => true],
                    ['name' => 'new_string', 'type' => 'string', 'description' => '替换后的新内容；删除内容时传空字符串', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($patches, $assertSuperAdmin): array {
                    $assertSuperAdmin($context);
                    $file = is_string($input['file'] ?? null) ? $input['file'] : '';
                    $oldString = is_string($input['old_string'] ?? null) ? $input['old_string'] : '';
                    $newString = is_string($input['new_string'] ?? null) ? $input['new_string'] : '';
                    if ($file === '' || $oldString === '') {
                        throw new \DomainException('缺少 file 或 old_string');
                    }
                    $patch = $patches->propose($context, $file, $oldString, $newString);
                    if (!$context->autoApprove) {
                        $patch['message'] = '已生成待批准改动。请展示 diff，并提示用户点击「批准」应用或「拒绝」放弃。';
                        return $patch;
                    }
                    $review = $patches->reviewById((int)$patch['id'], $context->tenantId, $context->adminId, $context->control);
                    $context->control?->check(true);
                    if (!empty($review['approve'])) {
                        $applied = $patches->apply((int)$patch['id'], $context->tenantId, $context->adminId, 'auto', (string)($review['reason'] ?? ''));
                        return [
                            'id' => $applied['id'],
                            'file' => $applied['file'],
                            'status' => 'applied',
                            'auto_approved' => true,
                            'review' => $review,
                            'message' => 'AI 自审通过并已应用改动，可用 source_rollback 回退。',
                        ];
                    }
                    $patch['auto_approved'] = false;
                    $patch['review'] = $review;
                    $patch['message'] = 'AI 自审未通过，转人工批准：' . (string)($review['reason'] ?? '');
                    return $patch;
                },
            ],
            [
                'name' => 'source_rollback',
                'approval' => '回退已应用的代码改动，需要人工批准',
                'label' => '回退代码改动',
                'description' => '把一个已应用的代码改动回退到改动前的备份内容。用于用户反馈改错了需要还原。',
                'permission' => 'auth.rule/edit',
                'properties' => [
                    ['name' => 'patch_id', 'type' => 'integer', 'description' => '要回退的改动 ID', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($patches, $assertSuperAdmin): array {
                    $assertSuperAdmin($context);
                    $id = filter_var($input['patch_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($id === false) {
                        throw new \DomainException('改动 ID 无效');
                    }
                    return $patches->rollback($id, $context->tenantId, $context->adminId);
                },
            ],
        ];
    }
}

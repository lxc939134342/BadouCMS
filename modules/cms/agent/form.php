<?php

use app\common\agent\AgentContext;
use modules\cms\agent\service\FormAgentService;

$fcode = ['name' => 'fcode', 'type' => 'string', 'description' => '表单查询结果的 fcode，不是表单 ID', 'required' => true];
$id = ['name' => 'id', 'type' => 'integer', 'description' => '表单字段读取结果的字段 ID', 'required' => true];

return [
    // 可选：后台助手首页的能力卡片；prompts 中列出的工具都可用时才展示该示例。
    'showcase' => [
        'title' => '表单与留言', 'icon' => 'fa-wpforms', 'description' => '管理自定义表单字段，查看提交记录',
        'prompts' => [
            ['text' => '查看在线留言表单最近的提交记录', 'tools' => ['cms_form_search', 'cms_form_submissions']],
        ],
    ],
    'guidelines' => '表单定义及字段跨语言共用，提交数据仅查询当前后台语言。先查询并读取再修改，不猜测 fcode 或字段 ID。字段目前为最长 255 字的文本。删除字段会永久删除该列的历史数据，须明确说明；删除表单保留物理表和历史提交。建表、增删字段及其他写入都须审批。',
    'tools' => [
        [
            'name' => 'cms_form_search', 'label' => '查询表单', 'description' => '查询自定义表单，返回 fcode、名称和已注册的数据表。表单定义跨语言共用。',
            'permission' => 'cms.form/index',
            'properties' => [['name' => 'keyword', 'type' => 'string', 'description' => '表单名称关键词'], ['name' => 'limit', 'type' => 'integer', 'description' => '默认 20，最多 50']],
            'handler' => static fn (AgentContext $context, array $input): array => (new FormAgentService())->search($input['keyword'] ?? '', $input['limit'] ?? 20),
        ],
        [
            'name' => 'cms_form_read', 'label' => '读取表单字段', 'description' => '读取表单定义及字段 ID、名称、说明、是否必填和排序。',
            'permission' => 'cms.form/index', 'properties' => [$fcode],
            'handler' => static fn (AgentContext $context, array $input): array => (new FormAgentService())->read($context, $input['fcode']),
        ],
        [
            'name' => 'cms_form_create', 'label' => '添加表单', 'description' => '添加自定义表单并创建数据表，自动使用 cms_diy_ 前缀。创建后可继续添加字段。',
            'permission' => 'cms.form/add', 'approval' => true,
            'properties' => [['name' => 'form_name', 'type' => 'string', 'description' => '表单中文名称，最多 30 字', 'required' => true], ['name' => 'table_name', 'type' => 'string', 'description' => '表名后缀，如 inquiry；1～22 位小写字母、数字、下划线，以字母开头', 'required' => true]],
            'handler' => static fn (AgentContext $context, array $input): array => (new FormAgentService())->create($context, $input['form_name'], $input['table_name']),
        ],
        [
            'name' => 'cms_form_update', 'label' => '修改表单名称', 'description' => '修改已有表单名称，保留表名、字段和提交数据。',
            'permission' => 'cms.form/edit', 'approval' => true,
            'properties' => [$fcode, ['name' => 'form_name', 'type' => 'string', 'description' => '新的表单名称', 'required' => true]],
            'handler' => static fn (AgentContext $context, array $input): array => (new FormAgentService())->rename($context, $input['fcode'], $input['form_name']),
        ],
        [
            'name' => 'cms_form_delete', 'label' => '删除表单定义', 'description' => '删除表单和字段定义，保留物理表及历史提交数据。系统的在线留言表单不能删除。',
            'permission' => 'cms.form/del', 'approval' => true, 'properties' => [$fcode],
            'handler' => static fn (AgentContext $context, array $input): array => (new FormAgentService())->delete($context, $input['fcode']),
        ],
        [
            'name' => 'cms_form_field_create', 'label' => '添加表单字段', 'description' => '给已有表单增加文本字段，同时增加真实数据列，最长 255 字。',
            'permission' => 'cms.form_field/add', 'approval' => true,
            'properties' => [$fcode, ['name' => 'name', 'type' => 'string', 'description' => '字段英文名称，如 contact_name；不能用 id、acode、create_time', 'required' => true], ['name' => 'description', 'type' => 'string', 'description' => '字段中文说明', 'required' => true], ['name' => 'required', 'type' => 'boolean', 'description' => '是否必填，默认 false'], ['name' => 'sorting', 'type' => 'integer', 'description' => '排序，默认 255']],
            'handler' => static fn (AgentContext $context, array $input): array => (new FormAgentService())->addField($context, $input),
        ],
        [
            'name' => 'cms_form_field_update', 'label' => '修改表单字段', 'description' => '修改表单字段的说明、必填要求或排序，保留字段名称和历史数据。',
            'permission' => 'cms.form_field/edit', 'approval' => true,
            'properties' => [$id, ['name' => 'description', 'type' => 'string', 'description' => '新的中文说明'], ['name' => 'required', 'type' => 'boolean', 'description' => '是否必填'], ['name' => 'sorting', 'type' => 'integer', 'description' => '排序']],
            'handler' => static fn (AgentContext $context, array $input): array => (new FormAgentService())->updateField($context, $input),
        ],
        [
            'name' => 'cms_form_field_delete', 'label' => '删除表单字段', 'description' => '删除字段定义和真实数据列，会永久删除该字段的历史提交内容，须告知用户后请求审批。',
            'permission' => 'cms.form_field/del', 'approval' => '删除字段会永久删除该列数据，请确认', 'properties' => [$id],
            'handler' => static fn (AgentContext $context, array $input): array => (new FormAgentService())->deleteField($context, $input['id']),
        ],
        [
            'name' => 'cms_form_submissions', 'label' => '查询表单提交', 'description' => '分页读取已注册表单在当前后台语言下的提交记录，不接受任意数据库表名。',
            'permission' => 'cms.form_data/index',
            'properties' => [$fcode, ['name' => 'page', 'type' => 'integer', 'description' => '页码，默认 1'], ['name' => 'limit', 'type' => 'integer', 'description' => '每页条数，默认 20，最多 50']],
            'handler' => static fn (AgentContext $context, array $input): array => (new FormAgentService())->submissions($input['fcode'], $input['page'] ?? 1, $input['limit'] ?? 20),
        ],
    ],
];

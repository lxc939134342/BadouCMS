<?php

use app\common\agent\AgentContext;
use modules\cms\agent\service\CategoryAgentService;

$updateProperties = [
    ['name' => 'language', 'type' => 'string', 'description' => '栏目详情返回的 language', 'required' => true],
    ['name' => 'scode', 'type' => 'string', 'description' => '已查询的目标栏目 scode，不能使用数据库 id', 'required' => true],
    ['name' => 'snapshot', 'type' => 'string', 'description' => 'cms_category_read 返回的原始 snapshot，用于防止覆盖审批期间的修改', 'required' => true],
];
foreach (CategoryAgentService::EDITABLE_FIELDS as $field => [$label, $length]) {
    $updateProperties[] = ['name' => $field, 'type' => $field === 'sorting' ? 'integer' : 'string',
        'description' => $label . '，最多 ' . $length . ' 字；仅传用户要修改的字段，空字符串表示清空可选字段'];
}

return [
    // 可选：后台助手首页的能力卡片；prompts 中列出的工具都可用时才展示该示例。
    'showcase' => [
        'title' => '栏目管理', 'icon' => 'fa-sitemap', 'description' => '新增和修改栏目，自动生成英文编码与 URL',
        'prompts' => [
            ['text' => '新增一个“解决方案”栏目', 'tools' => ['cms_category_search', 'cms_category_create']],
            ['text' => '列出当前语言的所有栏目', 'tools' => ['cms_category_search']],
        ],
    ],
    'guidelines' => '添加栏目或文章前先调用 cms_category_search 查询栏目和已启用模型。用户要求新增栏目时，自动把中文栏目名称翻译为简洁英文，生成非空的 aucode（通用编码，小写英文单词用下划线连接）和 filename（URL 名称，小写英文单词用连字符连接），不要使用拼音或随机编码，不要求用户手工填写。例如服务解决方案对应 service_solutions 和 service-solutions。查询到同类栏目有一致通用编码前缀（如 geo_）时沿用该前缀，但 URL 名称不添加该前缀。使用已加载的 cms_category_create 提交 language、name、mcode、pcode、aucode 和 filename 并等待界面审批；同语言编码或 URL 已存在时换用明确的英文名称或编号后重新提交，不能覆盖已有栏目。只有新增成功返回的 scode 才能用于后续 cms_article_create；不猜测 scode、mcode，不把提交审批当作新增成功。使用查询结果的语言与模型；父栏目必须属于当前语言，目标不唯一时询问用户。修改已有栏目先用 cms_category_read 读取全部可编辑字段和 snapshot，再用 cms_category_update 提交原始 language、scode、snapshot 及用户指定的修改字段；不要用新增代替编辑，不回传全部字段，不清空未指定字段。可选字段需清空时显式传空字符串，顶级父栏目填 0。补齐通用编码和 URL 名称时按中文名称生成英文；仅改名称不自动改 URL 或通用编码。通用编码用于跨语言关联，只有用户明确要求才修改，不自动改其他语言。版本过期时重新读取再生成修改申请，不猜测 snapshot；审批提交不等于保存成功。',
    'tools' => [
        [
            'name' => 'cms_category_search', 'label' => '查询栏目',
            'description' => '查询当前后台语言下的栏目和已启用内容模型，返回 scode、pcode、mcode、aucode、filename、language；用于新增栏目或文章前确定目标和英文命名规范。',
            'permission' => 'cms.content_sort/index',
            'properties' => [
                ['name' => 'keyword', 'type' => 'string', 'description' => '栏目名称关键词，留空查询全部'],
                ['name' => 'limit', 'type' => 'integer', 'description' => '返回数量，默认 20，最多 50'],
            ],
            'handler' => static fn (AgentContext $context, array $input): array =>
                (new CategoryAgentService())->search($input['keyword'] ?? '', $input['limit'] ?? 20),
        ],
        [
            'name' => 'cms_category_read', 'label' => '读取栏目详情',
            'description' => '读取当前后台语言指定栏目的全部可编辑字段、会员等级选项及版本 snapshot。修改前必须读取，支持隐藏栏目；scode 使用栏目查询结果。',
            'permission' => 'cms.content_sort/index',
            'properties' => [
                ['name' => 'scode', 'type' => 'string', 'description' => '栏目查询结果的 scode', 'required' => true],
            ],
            'handler' => static fn (AgentContext $context, array $input): array =>
                (new CategoryAgentService())->read($context, (string)($input['scode'] ?? '')),
        ],
        [
            'name' => 'cms_category_update', 'label' => '修改栏目',
            'description' => '修改当前后台语言已有栏目的任意可编辑字段，包括名称、通用编码、URL 名称、父栏目、模型、模板、图片、SEO、描述、状态、排序和访问权限。先读取详情，使用原始 snapshot；只传要改的字段，省略的字段保留，空字符串清空可选字段。不会创建重复栏目，不修改系统编号和审计字段。',
            'permission' => 'cms.content_sort/edit', 'approval' => true,
            'properties' => $updateProperties,
            'approval_validate' => static fn (AgentContext $context, array $input): array =>
                (new CategoryAgentService())->prepareUpdate($context, $input),
            'approval_display' => static fn (AgentContext $context, array $input): array =>
                (new CategoryAgentService())->approvalDisplay($context, $input),
            'handler' => static fn (AgentContext $context, array $input): array =>
                (new CategoryAgentService())->update($context, $input),
        ],
        [
            'name' => 'cms_category_create', 'label' => '添加栏目',
            'description' => '为当前后台语言添加栏目，支持顶级和子栏目。自动将中文栏目名翻译成英文并填写通用编码 aucode 和 URL 名称 filename，不能留空。使用已查询的内容模型与父栏目编码；单页模型会沿用 CMS 模型事件创建单页。',
            'permission' => 'cms.content_sort/add', 'approval' => true,
            'properties' => [
                ['name' => 'language', 'type' => 'string', 'description' => '栏目查询结果的 language', 'required' => true],
                ['name' => 'name', 'type' => 'string', 'description' => '栏目名称，最多 100 字', 'required' => true],
                ['name' => 'mcode', 'type' => 'string', 'description' => '已启用模型的 mcode；列表模型用于文章，单页模型用于关于我们等页面', 'required' => true],
                ['name' => 'aucode', 'type' => 'string', 'description' => '根据栏目名称生成的英文通用编码，最多 100 字，仅小写字母、数字、下划线；如 service_solutions，已有统一前缀时用 geo_service_solutions', 'required' => true],
                ['name' => 'filename', 'type' => 'string', 'description' => '根据栏目名称生成的英文 URL 名称，最多 30 字，仅小写字母、数字、连字符、下划线；如 service-solutions，不含目录或扩展名', 'required' => true],
                ['name' => 'pcode', 'type' => 'string', 'description' => '父栏目的 scode，顶级栏目填 0，默认 0'],
                ['name' => 'listtpl', 'type' => 'string', 'description' => '列表模板相对文件名，如 news.html；省略时使用模型默认模板'],
                ['name' => 'contenttpl', 'type' => 'string', 'description' => '内容模板相对文件名，如 newsinfo.html；省略时使用模型默认模板'],
                ['name' => 'description', 'type' => 'string', 'description' => '栏目 SEO 描述'],
            ],
            'approval_validate' => static fn (AgentContext $context, array $input): array =>
                (new CategoryAgentService())->identifiers($input),
            'handler' => static fn (AgentContext $context, array $input): array =>
                (new CategoryAgentService())->create($context, $input),
        ],
    ],
];

<?php

use app\common\agent\AgentContext;
use modules\cms\agent\service\ArticleAgentService;

/**
 * CMS 文章工具配置，由同目录的 config.php 引入。
 * 新增功能：在 tools 中增加一项，并在 service/ArticleAgentService.php 中实现业务方法。
 * 每个工具必填 name、description、permission、properties、handler，其他字段按需填写。
 * 单个工具也可配置 scenes、guidelines，覆盖本组的默认场景或使用规则。
 */
return [
    // 可选：后台助手首页的能力卡片；prompts 中列出的工具都可用时才展示该示例。
    'showcase' => [
        'title' => '内容创作', 'icon' => 'fa-pencil-square-o', 'description' => '按栏目撰写、查询、摘要和发布文章',
        'prompts' => [
            ['text' => '帮我写一篇介绍公司服务的文章，放到合适的栏目', 'tools' => ['cms_category_search', 'cms_article_create']],
            ['text' => '查询最近更新的文章', 'tools' => ['cms_article_search']],
        ],
    ],
    // 可选：工具开放给哪些助手。admin 是后台总助手，cms.content 是 ContentAgent 的场景。
    // 未填写时继承 config.php 的场景；入口也未填写时默认 ['admin']。场景名须与助手的 scene() 一致。
    'scenes' => ['admin', 'cms.content'],
    // 可选：本组工具的共同使用规则，告诉模型操作顺序和注意事项；权限与审批仍由代码检查。
    'guidelines' => '文章操作只处理当前后台语言。用户要求撰写文章并放入指定栏目时，先用 cms_category_search 确定栏目，再撰写标题和正文，用 cms_article_create 提交完整的 language、scode、title、content，保存为草稿并等待审批；此流程不需要查询插件功能。修改或发布前先查询并读取，目标不唯一时询问用户。摘要只是预览。发布等待用户在审批卡片上批准，不能声称未执行的操作已完成。',
    // 每个数组代表一个可供 AI 调用的操作。“查询文章”示例说明各字段，其他工具沿用相同结构。
    'tools' => [
        [
            'name' => 'cms_article_create',
            'label' => '添加文章',
            'description' => '在当前后台语言的列表栏目中添加文章草稿。先查询栏目确定 scode；不会直接发布。',
            'permission' => 'cms.content/add',
            'approval' => true,
            'properties' => [
                ['name' => 'language', 'type' => 'string', 'description' => '栏目查询结果的 language，须与当前后台语言一致', 'required' => true],
                ['name' => 'scode', 'type' => 'string', 'description' => '栏目查询结果的 scode，不是栏目 ID', 'required' => true],
                ['name' => 'title', 'type' => 'string', 'description' => '文章标题，最多 100 字', 'required' => true],
                ['name' => 'content', 'type' => 'string', 'description' => '正文，可使用安全 HTML，最多 100000 字', 'required' => true],
                ['name' => 'description', 'type' => 'string', 'description' => '文章描述，最多 500 字'],
                ['name' => 'keywords', 'type' => 'string', 'description' => 'SEO 关键词，最多 200 字'],
                ['name' => 'author', 'type' => 'string', 'description' => '作者，默认当前管理员昵称'],
                ['name' => 'source', 'type' => 'string', 'description' => '文章来源'],
                ['name' => 'ico', 'type' => 'string', 'description' => '已上传的缩略图地址'],
            ],
            'handler' => static fn (AgentContext $context, array $input): array =>
                (new ArticleAgentService())->create($context, $input),
        ],
        [
            // 必填：全项目唯一的工具名，建议使用“插件_业务_动作”；限 1～64 个字母、数字、下划线或连字符。
            'name' => 'cms_article_search',
            // 可选：界面显示的中文名称，如“正在调用工具：查询文章”；未填写时使用 name。
            'label' => '查询文章',
            // 必填：给模型看的功能描述，写清用途、返回内容和限制，帮助模型判断何时调用。
            'description' => '按标题搜索当前后台语言下的 CMS 文章，返回匹配总数和最多 20 条结果。',
            // 必填：对应现有后台权限节点。有权限才开放工具，实际执行前还会再次检查。
            'permission' => 'cms.content/index',
            // 必填：模型需要提供的参数列表，无参数时填 []。
            // 每个参数必填 name 和 type；type 可填 string、integer、number、boolean、array、object。
            // description 说明参数含义；required 默认 false；enum 可限定取值，如 ['draft', 'published']。
            // nullable 默认 false，设为 true 表示允许传 null；业务方法也需能处理这个值。
            'properties' => [
                [
                    'name' => 'keyword', // 参数名，对应执行函数中的 $input['keyword']。
                    'type' => 'string', // 参数值的类型，这里要求字符串。
                    'description' => '标题关键词；空字符串查看最新文章',
                    'required' => true, // 必须提供这个参数；允许空字符串与是否必填是两回事。
                ],
                // 可选参数的默认值在 handler 或 Service 中处理；description 中写“默认”不会自动赋值。
                ['name' => 'limit', 'type' => 'integer', 'description' => '返回数量，默认 10，最多 20'],
            ],
            // 必填：真正执行工具的函数，固定接收当前请求上下文 $context 和模型参数数组 $input，返回数组。
            // $context 提供管理员、会话等服务器身份；业务逻辑写在 Service 中，调用时创建 Service 实例。
            'handler' => static fn (AgentContext $context, array $input): array =>
                (new ArticleAgentService())->search($input['keyword'], $input['limit'] ?? 10),
        ],
        [
            'name' => 'cms_article_read',
            'label' => '读取文章',
            'description' => '读取当前后台语言下的 CMS 文章正文，最多 12000 字。',
            'permission' => 'cms.content/index',
            'properties' => [['name' => 'id', 'type' => 'integer', 'description' => '文章 ID', 'required' => true]],
            'handler' => static fn (AgentContext $context, array $input): array =>
                (new ArticleAgentService())->read($input['id']),
        ],
        [
            'name' => 'cms_article_summarize',
            'label' => '生成文章摘要',
            'description' => '生成文章摘要供预览，不保存或发布。',
            'permission' => 'cms.content/edit',
            'properties' => [['name' => 'id', 'type' => 'integer', 'description' => '文章 ID', 'required' => true]],
            'handler' => static fn (AgentContext $context, array $input): array =>
                (new ArticleAgentService())->summarize($context, $input['id']),
        ],
        [
            'name' => 'cms_article_publish',
            'label' => '发布文章',
            'description' => '发布指定 CMS 文章。须先读取并确认唯一目标，系统将请求人工审批。',
            'permission' => 'cms.content/multi',
            // 可选：默认 false；true 或审批原因文字表示需要审批，如 '发布文章需要批准'。
            // 是否自动批准由当前会话设置控制；没有配置 approval 的工具不会因描述中提到审批就暂停。
            'approval' => true,
            'properties' => [['name' => 'id', 'type' => 'integer', 'description' => '文章 ID', 'required' => true]],
            'handler' => static fn (AgentContext $context, array $input): array =>
                (new ArticleAgentService())->publish($input['id']),
        ],
    ],
];

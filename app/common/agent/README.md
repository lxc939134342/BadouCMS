# 插件如何接入 AI

项目使用 Neuron AI 4.0.0。一次性生成通过 `AiService` 调用；需要让 AI 操作插件功能时，在插件的 `agent/config.php` 中声明工具。插件的 AI 配置、专属助手和 AI 业务 Service 统一放在 `agent/` 目录。两种方式共用模型设置和访问凭证。

## 1. 只需要生成文字

在插件的 Service 中调用公共入口，控制器继续使用默认的登录、权限和令牌验证：

```php
use app\common\agent\AgentContext;
use app\common\ai\AiService;

$context = AgentContext::fromAuth($this->auth);
$summary = AiService::text($context, 'cms.content/edit', '请给以下文章生成摘要：' . $content);
```

`text()` 返回字符串；`structured($context, $permission, $prompt, Output::class)` 返回 Neuron 根据输出类生成的结构化对象。该入口不注册任何后台工具，也不保存聊天历史。生成内容由业务代码决定是否保存，模型不能自行操作其他插件。

实际示例：`modules/cms/agent/service/ArticleAgentService.php::summarize()` 读取文章，生成摘要供预览，不写入或发布文章。业务文字应限定长度，并说明提供的资料不是指令。

## 2. 让后台 Agent 调用插件功能

新增 `modules/demo/agent/config.php`，返回数组即可自动注册，无需编写 Provider 类或修改插件主类：

```php
<?php

use app\common\agent\AgentContext;
use modules\demo\agent\service\OrderAgentService;

return [
    'scenes' => ['admin', 'demo.order'],
    'resident_tools' => ['demo_order_search'],
    'guidelines' => '查询订单只返回当前账号可见的数据。目标不唯一时询问用户。',
    'tools' => [
        [
            'name' => 'demo_order_search',
            'label' => '查询订单',
            'description' => '按订单号查询当前账号可见的订单。',
            'permission' => 'demo.order/index',
            'properties' => [
                ['name' => 'number', 'type' => 'string', 'description' => '订单号', 'required' => true],
            ],
            'handler' => static fn (AgentContext $context, array $input): array =>
                (new OrderAgentService())->findForAdmin($context->adminId, $input['number']),
        ],
    ],
];
```

这里的 `OrderAgentService` 是开发者自己的 AI 业务代码，放在 `agent/service/OrderAgentService.php`。`handler` 固定接收服务器创建的 `AgentContext` 和工具参数数组，返回结果数组。AI 专用的查询、校验和业务操作集中写在 Agent 的 Service 中，通过现有 Model 的 ORM 访问数据；配置里只声明工具并调用 Service，不往原有业务 Model 中增加 Agent 方法。

在 `handler` 内创建 Service，每次执行使用新实例，避免长期保存的工具配置持有同一个 Service 对象。当前账号和会话信息通过本次调用的 `$context` 获取，不捕获配置加载时的用户状态。

工具名称统一使用 `插件_业务域_动作`，例如 `cms_article_read`。`permission` 是项目已有后台权限节点。工具可以自行声明 `scenes`，未声明时继承当前配置组的场景。

配置入口支持这些字段：

| 字段 | 用途 |
| --- | --- |
| `tools` | 工具定义数组；可以继续添加多个工具 |
| `guidelines` | 给模型的使用说明；权限和审批由代码强制执行 |
| `scenes` | 该组工具的默认场景，默认 `['admin']` |
| `groups` | 按业务分组，每组包含 `tools`、`guidelines`、`scenes` |
| `resident_tools` | 插件声明的常驻工具名列表，分组默认继承入口清单，也可自行覆盖；未配置为 `[]` |

功能较多时，将各组拆成小写业务名的配置文件。CMS 的实际入口是：

```php
// modules/cms/agent/config.php
return [
    'scenes' => ['admin', 'cms.content'],
    'groups' => [
        'article' => require __DIR__ . '/article.php',
        'category' => require __DIR__ . '/category.php',
        'settings' => require __DIR__ . '/settings.php',
        'form' => require __DIR__ . '/form.php',
        'language' => require __DIR__ . '/language.php',
        'translation' => require __DIR__ . '/translation.php',
        'task' => require __DIR__ . '/task.php',
    ],
];
```

CMS 的目录约定如下：

```text
modules/cms/agent/
├── config.php                     # 统一配置入口
├── article.php                    # 文章工具和使用规则
├── category.php                   # 栏目工具
├── settings.php                   # 站点、公司信息工具
├── form.php                       # 表单和字段工具
├── language.php                   # 跨语言复制工具
├── translation.php                # 手动译文预览和保存
├── task.php                       # 整项后台复制翻译任务
├── task/LanguageTranslateTask.php  # 通用队列中的 CMS 翻译处理器
├── ContentAgent.php               # 可选的 CMS 专属助手
└── service/
    ├── CmsAgentService.php         # 共用校验和写入锁
    ├── ArticleAgentService.php     # 文章 AI 业务与数据访问
    ├── CategoryAgentService.php
    ├── SettingsAgentService.php
    ├── FormAgentService.php
    ├── LanguageAgentService.php
    └── TranslationAgentService.php
```

CMS 已注册 30 个工具，覆盖文章、栏目、站点/公司信息、表单、跨语言复制与翻译和持久化后台任务，详情及 worker 部署见 [CMS Agent 说明](../../../modules/cms/agent/README.md)。新增独立业务时，建立对应配置文件并加入 `config.php` 的 `groups`。配置文件使用小写业务名，Service 使用 `业务名AgentService.php`，专属助手使用 `业务名Agent.php`。

后台任务默认兼容 FPM，无需部署 CLI；响应结束后按批处理，页面自动接续已批准任务。配置 `agent.queue.mode='cli'` 后由 `php think agent:task --watch` 独立消费，页面只展示进度。任务使用 `app/common/agent/model/AgentTask.php`，执行器和处理器统一注册，模式、自动恢复及关闭页面的限制见 [队列使用说明](../../../docs/agent-task-queue.md)。

CMS 的文章查询范围、关键词筛选、ID 校验和发布逻辑都在 `ArticleAgentService` 内；原有 `app/admin/model/cms/Content.php` 不添加 AI 专用代码。发布仍使用现有 Model 的 `save()`，沿用时间戳和缓存清理等模型事件。

共享规则和默认场景可以写在 `config.php`，分组规则会追加共享规则；没有任何可用工具的分组，其规则不会传给模型。分组只支持一层。

工具定义的字段如下：

| 字段 | 用途 |
| --- | --- |
| `name`、`label`、`description` | 唯一名称、中文名称和功能描述 |
| `permission` | 必填的后台权限节点 |
| `properties` | 参数声明，包含 `name`、`type`、`description`、`required` 等 |
| `handler` | 接收身份和参数，调用业务 Service |
| `approval` | 发布、删除等操作设置为 `true` 或审批原因文字 |
| `allow_auto_approve` | 布尔值，默认 `false`；允许用户的自动批准设置作用于该工具，权限检查不变 |
| `scenes`、`guidelines` | 覆盖当前工具的默认场景或规则 |
| `resident` | 布尔值，默认由插件的 `resident_tools` 决定；单个工具可覆盖是否常驻 |

执行逻辑复杂时，仍可继承 `AbstractAgentTool`，并在 `tools` 中放入工具对象；它使用类中声明的元数据。数组和工具对象可以混合使用。注册中心优先读取 `agent/config.php`，继续兼容旧的插件根目录 `agent.php` 和主类 Provider；只使用一个入口，避免重复注册。

注册中心按启用状态、模块范围、场景和后台权限过滤工具。插件定义错误只隔离该插件并记录日志。执行前再次检查账号权限和插件状态。工具标签来自工具定义，新增插件无需修改聊天服务的标签表。

后台总助手通过常驻工具 `module_capabilities(name: 'cms')` 查看插件功能。它分别返回当前账号可见的后台菜单和注册中心中已授权的 AI 工具，新增插件无需另写功能清单。后台菜单存在不代表 Agent 已能执行；`agent` 文件夹中的业务配置需由 `agent/config.php` 引入。

模型只返回工具名、没有必填参数时，不会执行该调用；尚未输出内容的流式请求会用相同上下文尝试一次普通响应恢复，仍缺参数则明确报错。Neuron 返回的 `ToolOutput::error()` 也会显示为调用失败。

工具可声明只读 `approval_validate(context, input)`，在进入审批前校验范围、权限和快照；不得在此回调执行业务写入。校验失败不生成待批准卡，执行层把同次调用的失败交回模型纠正，即使数据在两次校验之间恢复也不能绕过审批。有效请求继续走原生审批，实际执行仍重新校验。`ApprovalExpiredException` 的中文消息用于界面，`instructions` 仅作为模型恢复提示。

已暂停的 CMS 单项译文审批过期时，界面提供“重新读取并生成译文”，发送 `decision=refresh` 和原 `run_id` / `attempt`。后端核对账号、运行版本、工具及可恢复状态，只停止旧保存并恢复模型读取流程；重新生成的译文仍需另一次有效审批。其他操作和权限错误不能使用此入口。

## 3. 建立插件专属 Agent

继承 `BaseAgent`，只声明场景、模块和指令：

```php
class OrderAgent extends \app\common\agent\BaseAgent
{
    protected function scene(): string { return 'demo.order'; }
    protected function toolModules(): array { return ['demo']; }
    protected function coreTools(): array { return ['demo_order_search']; }

    protected function instructions(): string
    {
        return parent::instructions() . '你是订单助手。';
    }
}
```

调用只需要两步：

```php
$agent = new OrderAgent(AgentContext::fromAuth($this->auth));
$reply = (new \app\common\agent\AgentService())->ask($agent, $question);
```

`$reply` 包含 `answer`、`tools`、`approval`、`run_id`、`question`。传入第三个 `$progress` 回调可以接收 `progress`、`delta`、`tool_start`、`tool_done`、`tool_error` 事件。

`toolModules()` 默认 `[]`，不开放任何工具。插件声明 `['demo']` 后只能访问该插件；`['core']` 开放后台公共工具；`AdminAgent` 使用 `['*']` 开放所有已启用模块。`coreTools()` 声明当前助手自己的常驻工具；插件应在自身的 `agent/config.php` 声明 `resident_tools`，由公共加载器合并已授权的常驻定义，不把插件工具名或业务指令写入 `AdminAgent.php`。旧 Provider 可覆盖 `residentTools()`，未声明时保持按需发现。其他授权工具由 Neuron 的 `tool_search` 按需发现。检索支持中文短语与 CMS 等缩写；找到后应直接调用。每轮最多查找三次，达到上限会明确停止，避免模型持续重复查找。

实际可调用的 CMS 专属助手在 `modules/cms/agent/ContentAgent.php`，提供已注册的 CMS 工具，不提供会员或代码工具。常用查询和语言复制流程工具常驻，其他工具按需检索。

CMS 的 `agent/config.php` 通过 `resident_tools` 为后台总助手和 CMS 专属助手声明 `cms_category_search`、`cms_category_create`、`cms_article_create`，使查询栏目后新增、下一轮确认新增，以及新增成功后创建文章的流程都能直接获得完整工具 schema；只有当前账号已授权且模块启用的工具会加载。撰写文章、栏目新增及语言复制翻译的指令位于 CMS 各工具组的 `guidelines`。栏目新增成功返回的 `scode` 才能用于后续文章，提交审批不能当成已新增。其他工具在每轮通过 `tool_search` 按需加载，模型返回的工具调用按原工具名处理，不自动改写为检索调用。写操作仍走原审批流程。

后台总助手和 CMS 专属助手均常驻语言复制、手动翻译和 `cms_language_task_preview`、`cms_language_task_start`、`cms_language_task_status`，避免下一轮模型根据历史直接调用时工具尚未加载。批量翻译默认提交整项后台任务；复制后翻译使用 `copy_first=true`，只确认一次，内部批次自动保存。仅明确要求逐批审阅时使用手动翻译工具。只复制栏目使用 `include_content=false`、`include_settings=false`，任务使用 `kind=categories`。仍受模块启用状态和后台权限过滤。

后台总助手和 CMS 专属助手均常驻 `attachment_search`、`attachment_read_text`，受 `general.attachment/index` 权限过滤。上传接口返回附件 ID，正文工具的 `attachment` 接受该 ID 的字符串或已注册的 `/uploads/` 地址。读取本地 `.docx` 的正文、段落和表格文字，每次默认 12000 字，最多 16000 字；`has_more=true` 时按 `next_offset` 继续读取。不会自动导入图片、页眉页脚和 Word 排版，旧版 `.doc` 需另存为 `.docx`。该工具使用 PHP zip、xmlreader 扩展（现有 PhpSpreadsheet 的依赖），兼容 FPM，不需要 CLI 或新增数据库表。

附件正文读取独立于 `source_read` 的代码访问策略：必须存在附件记录，只允许本地上传目录内的真实文件，不接受外部 URL、路径越界或越界软链接；同时限制压缩包正文大小并禁止 XML 外部实体。CMS 场景只增加这两个只读附件工具，不开放源码工具。读到正文后，再调用现有 CMS 工具和审批流程写入内容，不能仅根据文件名声称已读取或已保存。

工具定义可提供只读的 `approval_display(context, inputs)` 回调，用当前数据生成审批摘要。公共层仅调用已授权工具的回调，详情失效时返回错误提示；原生审批参数不变，浏览器仍只提交决策。CMS 复制卡展示实际范围与数量，翻译卡展示原文/译文；内部校验标识保留在折叠参数详情中。

`config/agent.php` 的 `stream_tools` 默认 `true`，普通文字及工具执行后的回答实时通过 SSE 输出。工具参数仍需完整接收并通过校验后才执行或进入审批；尚未输出文字就遇到空响应、断流或缺少必填工具参数时，用相同上下文尝试一次普通响应恢复，不重新执行已完成的工具。已输出文字后不自动重试，避免重复回答。仅在网关不支持流式响应时设为 `false` 使用完整响应兼容模式；这也会使普通文字等整段生成后才显示。队列任务的进度轮询与对话流式输出独立。

## 4. 写操作的审批

在发布、删除等工具定义中声明：

```php
'approval' => '发布文章需要人工批准',
```

使用工具类时，对应声明为 `protected bool|string $approval = '发布文章需要人工批准';`。

模型调用该工具后，Neuron 会暂停执行并保存待审批的参数。`$reply['approval']` 不为空表示等待用户批准，不是错误或空回答。用户决定后使用同一个 Agent 类和会话身份继续：

```php
$service = new \app\common\agent\AgentService();
$agent = new OrderAgent($context);
$reply = $service->resume($agent, $runId, $attempt, 'approve'); // 或 reject
// 保存 $reply 到业务会话后再确认完成，待审批状态不会被清理。
$service->acknowledge($agent, $reply);
```

`run_id` 和 `attempt` 来自服务器返回的审批快照；浏览器只提交这两个值和决策，不提交或修改工具参数。后台聊天界面通过已有 `agent/send` 接口处理审批，继续使用默认权限及 CSRF 验证。重复或过期决策会被拒绝，权限收回或插件停用后不能批准，但仍可拒绝。

审批会话需要一个已经核对所属账号的 `AgentChat` ID，创建上下文时传入该 ID。`chatId=0` 用于一次性调用，不能跨 HTTP 请求恢复审批。聊天界面已处理保存、恢复和确认完成；自建插件界面须处理这三个步骤。

工作流与原始消息分别保存到 `agent_workflow`、`agent_message`；会话摘要保存在 `agent_chat`。新安装站点由 `extend/badou/install/badouadmin-install.sql` 创建 Agent 表；已有站点通过框架升级插件执行 `modules/upgrade/install.sql` 补建缺失表，不在业务请求中建表。升级 SQL 使用 `CREATE TABLE IF NOT EXISTS`，保留已有会话、审批和任务记录。MySQL/MariaDB 需开启严格 SQL 模式，这是 Neuron 数据库存储的要求。

用户消息支持复制和编辑重发。编辑通过 `agent/send` 的 `edit_message_id` 和 `edit_revision` 定位消息并校验会话版本，在当前会话回到该消息之前重新回答，不新增侧栏会话。旧问答与版本标识保存在同一 `messages` 字段的内部历史中，不发送给模型；未编辑的旧会话仍兼容原数组格式，无需修改数据库结构。每次编辑使用新的模型线程版本，仅导入编辑点之前的文字，不继承旧审批；旧版本审批不能通过当前会话继续执行。已执行的后台操作和队列任务不会撤销。保存回复时再次检查数据库版本，阻止旧请求覆盖编辑后的问答；删除会话同时清理各版本模型线程。旧会话的用户消息在读取时补充稳定标识，后续保存时落库。

运行成功后先保存业务回答，再 `acknowledge()` 清理完成状态。保存失败时 `recover($agent)` 能读取已完成结果，避免重新执行工具。数据库的版本校验保护重复审批；外部操作仍应由自己的业务 Service 实现幂等，不能把它理解成断电、外部 API 超时等场景下的业务“恰好一次”。

当前 `AgentContext::fromAuth()` 的 `tenantId` 为 0，尚不是完整多租户实现。权限节点决定能否使用功能，行级数据范围仍由业务 Service/Model 执行，例如 CMS 工具沿用后台语言和文章模型范围。不要接受模型传入的管理员身份或租户身份。

## 5. 兼容现有工具

旧 `AgentToolInterface` 和 `AgentToolProviderInterface` 继续可用，原有工具数组不必全部重写。新元数据如下：

| 字段 | 默认值 | 用途 |
| --- | --- | --- |
| `label` | 工具名 | 界面上的中文名称 |
| `scenes` | 配置组场景，或 `['admin']` | 允许使用的场景 |
| `approval` | `false` | `true` 或原因文字触发人工审批 |
| `guidelines` | 配置组或 Provider 说明 | 该领域的调用规则 |
| `module` | 注册中心填写 | 执行时核对插件状态 |

旧工具的 `confirm=false` 保留预览逻辑；声明 `approval => 'confirm'` 后，`confirm=true` 会触发人工审批。模型生成的 `confirm=true` 不是用户批准。代码改动卡片仍使用原有补丁审批流程；“自动批准”只额外开放给明确声明 `allow_auto_approve=true` 的工具，目前仅 CMS 整项后台任务提交。发布、删除、单独保存译文等工具仍需审批。

模型请求失败最多重试一次：限流、网关异常、真正的繁忙分别提示；空流或尚未输出内容就断流时尝试普通响应。重试只复用当前消息和工具结果，不重新运行整个 Agent。New API 中 HTTP 成功与有效模型输出是两个指标，流式请求输出 Token 为 0 时仍可能触发恢复。

## 手动停止

后台聊天生成期间，发送按钮变为“停止”。停止请求和发送请求共用 `agent/send` 的权限与 CSRF 验证，通过 `request_id` 区分每一轮。新会话尚未返回会话 ID 时也能停止，旧停止请求不会影响下一轮或其他管理员。

`AgentRunControl` 的标记绑定服务器创建的身份，存放于项目缓存并自动过期。多台 Web 服务器应使用共享缓存，例如 Redis，确保停止请求与生成请求能读取同一状态。

公共层在模型传输、节点切换及每个工具执行前检查停止状态；停止不会触发模型重试。已生成的文字与已完成工具记录保留，工作流清理后可以继续当前会话。正在执行的单个业务操作安全结束，停止不撤销已完成的修改。代码自审和 CMS 摘要也沿用同一个停止标记。

插件自建界面若需要停止，可将 `AgentRunControl` 传入 `AgentContext::fromAuth()` 的第四个参数；停止接口使用同一个身份和请求标识调用 `stop()`，不得接收浏览器提交的管理员或租户身份。

## 验证

```sh
php tests/ai/agent-extension.php
php tests/ai/model-provider.php
node tests/ai/agent-session.cjs
node tests/ai/agent-stop.cjs
python3 tests/ai/agent-stop-http.py
php tests/ai/cms-tools.php
```

测试不调用真实模型或写入站点业务数据。公共层使用模拟模型和 SQLite；HTTP 停止测试仅启动临时的本地延迟响应服务器，验证等待首字与非流式响应时能断开连接。CMS 工具测试需要本机 MySQL 的建库、删库权限，创建独立临时数据库验证原 CMS 表结构（包括 MyISAM）和真实表单 DDL，结束后删除测试库。

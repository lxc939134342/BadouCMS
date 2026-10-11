<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = new think\App(dirname(__DIR__, 2) . '/');
$app->initialize();

use app\common\agent\{AbstractAgentTool, AbstractAgentToolProvider, AgentContext, AgentService, AgentToolExecutor, AgentToolRegistry, BaseAgent, NeuronToolAdapter};
use app\common\ai\AiModelProvider;
use app\common\library\AdminAuth;
use NeuronAI\Chat\History\{MessageStoreInterface, SQLMessageStore};
use NeuronAI\HttpClient\{HttpClientInterface, HttpRequest, HttpResponse, StreamInterface};
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Workflow\Persistence\{PersistenceInterface, DatabasePersistence};

function check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

class FixtureAuth extends AdminAuth
{
    public bool $allowed = true;
    public array $denied = [];
    public array $menus = [];
    public function __construct() {}
    public function check($name, $uid = '', $relation = 'or', $mode = 'url') { return $this->allowed && !in_array($name, $this->denied, true); }
    public function getMenus(int $uid = 0): array { return $this->menus; }
}

class FixtureTool extends AbstractAgentTool
{
    protected string $name = 'fixture_write';
    protected string $label = '测试写入';
    protected string $permission = 'fixture/edit';
    protected string $description = '测试审批';
    protected bool|string $approval = true;
    public static int $executions = 0;
    public function properties(): array { return [['name' => 'id', 'type' => 'integer', 'required' => true]]; }
    public function execute(AgentContext $context, array $input): array
    {
        self::$executions++;
        return ['id' => $input['id'], 'saved' => true];
    }
}

class FixtureProvider extends AbstractAgentToolProvider
{
    public function agentTools(): array { return [new FixtureTool()]; }
}

class FixtureRegistry extends AgentToolRegistry
{
    protected function modules(): array { return []; }
    protected function coreProviders(): array { return [new FixtureProvider()]; }
}

class FixtureHttp implements HttpClientInterface
{
    public array $offeredTools = [];
    public array $toolSchemas = [];
    public array $modes = [];
    public function __construct(private array $responses) {}
    public function request(HttpRequest $request): HttpResponse
    {
        $this->modes[] = 'chat';
        $this->offeredTools[] = array_column(array_column($request->body['tools'] ?? [], 'function'), 'name');
        $this->toolSchemas[] = array_column(array_column($request->body['tools'] ?? [], 'function'), null, 'name');
        if ($this->responses === []) throw new RuntimeException('不应发生额外模型请求');
        return new HttpResponse(200, json_encode(['choices' => [['message' => array_shift($this->responses), 'finish_reason' => 'stop']]]));
    }
    public function stream(HttpRequest $request): StreamInterface
    {
        $this->modes[] = 'stream';
        $this->offeredTools[] = array_column(array_column($request->body['tools'] ?? [], 'function'), 'name');
        $this->toolSchemas[] = array_column(array_column($request->body['tools'] ?? [], 'function'), null, 'name');
        if ($this->responses === []) throw new RuntimeException('不应发生额外模型请求');
        $delta = array_shift($this->responses);
        if (isset($delta['tool_calls'])) {
            foreach ($delta['tool_calls'] as $index => &$call) $call['index'] = $index;
            unset($call);
        }
        if (!isset($delta['tool_calls']) && is_string($delta['content'] ?? null) && $delta['content'] !== '') {
            // 两段独立的模型事件，防止只收到最终结果也被当作流式测试通过。
            $first = mb_substr($delta['content'], 0, 2);
            $last = mb_substr($delta['content'], 2);
            $body = '';
            foreach ([$first, $last] as $part) {
                $body .= 'data: ' . json_encode(['id' => 'fixture', 'choices' => [['index' => 0,
                    'delta' => ['content' => $part], 'finish_reason' => null]]]) . "\n\n";
            }
            $body .= 'data: ' . json_encode(['id' => 'fixture', 'choices' => [['index' => 0,
                'delta' => [], 'finish_reason' => 'stop']]]) . "\n\ndata: [DONE]\n\n";
            return new \NeuronAI\HttpClient\Guzzle\GuzzleStream(\GuzzleHttp\Psr7\Utils::streamFor($body), $request);
        }
        $body = 'data: ' . json_encode(['id' => 'fixture', 'choices' => [['index' => 0, 'delta' => $delta, 'finish_reason' => isset($delta['tool_calls']) ? 'tool_calls' : 'stop']]]) . "\n\ndata: [DONE]\n\n";
        return new \NeuronAI\HttpClient\Guzzle\GuzzleStream(\GuzzleHttp\Psr7\Utils::streamFor($body), $request);
    }
}

class FixtureAgent extends BaseAgent
{
    public static PDO $pdo;
    public static bool $enabled = true;
    public function __construct(AgentContext $context, private AIProviderInterface $fixtureModel) { parent::__construct($context); }
    protected function provider(): AIProviderInterface { $this->context->control?->check(true); return $this->fixtureModel; }
    protected function persistence(): PersistenceInterface { return new DatabasePersistence(self::$pdo, 'workflow_store'); }
    protected function messageStore(): MessageStoreInterface { return new SQLMessageStore(self::$pdo, 'messages'); }
    public function toolDefinitions(): array
    {
        return self::$enabled && $this->context->auth->allowed ? [(new FixtureTool())->definition() + ['module' => 'core']] : [];
    }
    protected function coreTools(): array { return ['fixture_write']; }
    protected function globalMiddleware(): array { return []; }
}

function model(array $responses): AIProviderInterface
{
    return new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: new FixtureHttp($responses));
}

class PluginFixtureRegistry extends AgentToolRegistry
{
    protected function modules(): array
    {
        return [['name' => 'broken', 'state' => 1], ['name' => 'cms', 'state' => 1], ['name' => 'disabled', 'state' => 0]];
    }
    protected function coreProviders(): array { return [new FixtureProvider()]; }
    protected function moduleProvider(string $name): ?\app\common\agent\AgentToolProviderInterface
    {
        return new class extends AbstractAgentToolProvider {
            public function agentTools(): array {
                $tool = (new FixtureTool())->definition();
                $tool['name'] = 'bad_schema';
                $tool['properties'] = [['name' => 'id', 'type' => 'unsupported']];
                return [$tool];
            }
        };
    }
}

$auth = new FixtureAuth();
$context = new AgentContext($auth, 1, 0, 1);
$registry = new FixtureRegistry();
check($registry->available($context, new AgentToolExecutor(), 'admin', []) === [], '默认 Agent 不应开放工具');
check(count($registry->available($context, new AgentToolExecutor(), 'admin', ['core'])) === 1, '工具注册失败');
check($registry->available($context, new AgentToolExecutor(), 'cms.content', ['core']) === [], '场景隔离失败');
$auth->allowed = false;
check($registry->available($context, new AgentToolExecutor()) === [], '无权限工具未过滤');
$auth->allowed = true;
$pluginExecutor = new class extends AgentToolExecutor { protected function moduleEnabled(string $name): bool { return true; } };
$plugins = new PluginFixtureRegistry();
$tools = $plugins->available($context, $pluginExecutor, 'admin', ['*']);
check(count($tools) === 96, '坏插件影响正常插件或后台工具');
$tools = $plugins->available($context, $pluginExecutor, 'cms.content', ['cms']);
check(count($tools) === 95 && array_unique(array_column($tools, 'module')) === ['cms'], 'CMS 专属场景泄漏其他工具');
check($plugins->available($context, $pluginExecutor, 'admin', ['disabled']) === [], '已禁用插件仍注册');

// 配置文件自动发现、分组规则和旧主类入口共用相同的权限与场景过滤。
$configFile = tempnam(sys_get_temp_dir(), 'agent-config-');
$brokenConfigFile = tempnam(sys_get_temp_dir(), 'agent-config-broken-');
try {
    file_put_contents($configFile, <<<'PHP'
<?php
$search = [
    'name' => 'fixture_config_search', 'label' => '配置查询', 'description' => '配置中的查询工具',
    'permission' => 'fixture/search',
    'properties' => [['name' => 'keyword', 'type' => 'string', 'required' => true]],
    'handler' => static fn ($context, $input): array => ['admin_id' => $context->adminId, 'keyword' => $input['keyword']],
];
return [
    'scenes' => ['admin', 'fixture.content'], 'guidelines' => '共享规则',
    'resident_tools' => ['fixture_config_search', 'fixture_config_write'],
    'groups' => [
        'search' => ['guidelines' => '查询规则', 'tools' => [$search]],
        'write' => ['scenes' => ['admin'], 'resident_tools' => [], 'guidelines' => '写入规则', 'tools' => [array_replace($search, [
            'name' => 'fixture_config_write', 'permission' => 'fixture/edit', 'approval' => true,
        ])]],
    ],
];
PHP);
    file_put_contents($brokenConfigFile, '<?php return ["tools" => "invalid"];');
    $configRegistry = new class($configFile, $brokenConfigFile) extends AgentToolRegistry {
        public int $legacyCalls = 0;
        public function __construct(private string $configFile, private string $brokenConfigFile) {}
        protected function modules(): array {
            return [
                ['name' => 'configured', 'state' => 1], ['name' => 'bad_config', 'state' => 1],
                ['name' => 'inactive_config', 'state' => 0], ['name' => 'legacy', 'state' => 1],
            ];
        }
        protected function coreProviders(): array { return []; }
        protected function moduleConfigFile(string $name): string {
            return match ($name) {
                'configured', 'inactive_config' => $this->configFile,
                'bad_config' => $this->brokenConfigFile,
                default => $this->configFile . '-missing',
            };
        }
        protected function moduleProvider(string $name): ?\app\common\agent\AgentToolProviderInterface {
            $this->legacyCalls++;
            return new FixtureProvider();
        }
    };
    $configured = $configRegistry->available($context, $pluginExecutor);
    check(count($configured) === 3 && $configRegistry->legacyCalls === 1, '配置重复注册、旧入口失效或坏配置影响其他插件');
    $scoped = $configRegistry->available($context, $pluginExecutor, 'fixture.content', ['configured']);
    check(array_column($scoped, 'name') === ['fixture_config_search'], '配置场景继承或分组覆盖失败');
    check($scoped[0]['guidelines'] === "共享规则\n查询规则", '分组规则没有合并');
    check($scoped[0]['resident'] === true, '分组工具没有继承插件常驻声明');
    $result = $pluginExecutor->execute($scoped[0], $context, ['keyword' => '栏目']);
    check($result === ['admin_id' => 1, 'keyword' => '栏目'], '数组工具执行没有收到身份与参数');
    $publish = array_values(array_filter($configured, fn ($tool) => $tool['name'] === 'fixture_config_write'))[0];
    check($publish['resident'] === false, '分组不能覆盖插件的常驻声明');
    check((new NeuronToolAdapter($pluginExecutor))->adapt($publish, $context)->requiresApproval(), '配置工具绕过人工审批');
    $auth->denied = ['fixture/edit'];
    $permitted = $configRegistry->available($context, $pluginExecutor);
    check(count($permitted) === 1 && !str_contains($permitted[0]['guidelines'], '写入规则'), '配置泄露未授权工具或分组规则');
    try {
        $pluginExecutor->execute($publish, $context, ['keyword' => '栏目']);
        throw new RuntimeException('配置工具执行前没有重新鉴权');
    } catch (DomainException $e) {}
    $auth->denied = [];
    $mixed = new \app\common\agent\AgentToolConfig(['tools' => [new FixtureTool(), $scoped[0]], 'resident_tools' => ['fixture_write']]);
    check(count($mixed->agentTools()) === 2, '配置不兼容工具类与数组混合声明');
    $mixedRegistry = new class($mixed) extends AgentToolRegistry {
        public function __construct(private $provider) {}
        protected function modules(): array { return []; }
        protected function coreProviders(): array { return [$this->provider]; }
    };
    $mixedTools = array_column($mixedRegistry->available($context, $pluginExecutor), null, 'name');
    check($mixedTools['fixture_write']['resident'] === true
        && (new NeuronToolAdapter($pluginExecutor))->adapt($mixedTools['fixture_write'], $context)->requiresApproval(),
        '对象工具的插件常驻声明未生效或改变了审批');
    foreach ([['tools' => 'invalid'], ['guidelines' => []], ['scenes' => [1]], ['tool' => []],
        ['resident_tools' => true], ['resident_tools' => [1]], ['resident_tools' => ['invalid/name']]] as $invalid) {
        try {
            new \app\common\agent\AgentToolConfig($invalid);
            throw new RuntimeException('配置错误字段未拒绝');
        } catch (LogicException $e) {}
    }
    file_put_contents($brokenConfigFile, '<?php return "invalid";');
    check(count($configRegistry->available($context, $pluginExecutor)) === 3, '非数组配置影响正常插件');
    file_put_contents($brokenConfigFile, '<?php return ["groups" => ["invalid"]];');
    check(count($configRegistry->available($context, $pluginExecutor)) === 3, '无效分组影响正常插件');
    file_put_contents($brokenConfigFile, '<?php return ["groups" => "invalid"];');
    check(count($configRegistry->available($context, $pluginExecutor)) === 3, '非数组分组影响正常插件');
    file_put_contents($brokenConfigFile, '<?php return ["groups" => [[], ["groups" => []]]];');
    check(count($configRegistry->available($context, $pluginExecutor)) === 3, '嵌套分组被静默忽略');
    file_put_contents($brokenConfigFile, '<?php $config = require ' . var_export($configFile, true) . '; $config["groups"]["write"]["tools"][0]["name"] = "fixture_config_search"; return $config;');
    check(count($configRegistry->available($context, $pluginExecutor)) === 3, '跨分组重复工具未隔离');
} finally {
    unlink($configFile);
    unlink($brokenConfigFile);
}

$articles = new \modules\cms\agent\service\ArticleAgentService();
foreach (['read', 'publish'] as $method) {
    try {
        $articles->$method(0);
        throw new RuntimeException('文章工具丢失 ID 校验');
    } catch (DomainException $e) { check($e->getMessage() === '请选择有效的文章', '文章 ID 校验提示变化'); }
}

$coreRegistry = new class extends AgentToolRegistry { protected function modules(): array { return []; } };
$core = $coreRegistry->available($context, new AgentToolExecutor());
check(count($core) > 30 && in_array('user_search', array_column($core, 'name'), true), '原有工具未兼容');
// 首页能力卡片：随插件启用状态、场景和权限过滤，缺少依赖工具的示例和空卡片不展示。
$cmsCards = $plugins->showcases($context, $pluginExecutor, 'admin', ['cms']);
check(array_column($cmsCards, 'title') === ['内容创作', '栏目管理', '表单与留言', '多语言站点']
    && array_unique(array_column($cmsCards, 'module')) === ['cms'] && !isset($cmsCards[0]['prompts'][0]['tools']), 'CMS 能力卡片未注册或泄露工具依赖');
check($plugins->showcases($context, $pluginExecutor, 'admin', ['disabled']) === [], '已禁用插件仍展示能力卡片');
$auth->denied = ['cms.content/add'];
$cmsCards = array_column($plugins->showcases($context, $pluginExecutor, 'admin', ['cms']), 'prompts', 'title');
check($cmsCards['内容创作'] === ['查询最近更新的文章'], '无权限工具的示例仍展示');
$auth->denied = ['cms.content/add', 'cms.content/index'];
check(!isset(array_column($plugins->showcases($context, $pluginExecutor, 'admin', ['cms']), null, 'title')['内容创作']), '无可用示例的卡片仍展示');
$auth->denied = [];
$coreCards = array_column($coreRegistry->showcases($context, new AgentToolExecutor()), 'title');
check(in_array('会员管理', $coreCards, true) && in_array('插件管理', $coreCards, true), '后台核心能力卡片缺失');
$badCard = new class extends AgentToolRegistry {
    protected function modules(): array { return []; }
    protected function coreProviders(): array {
        return [new \app\common\agent\AgentToolConfig(['tools' => [(new FixtureTool())->definition()],
            'showcase' => ['title' => '坏卡片', 'icon' => 'x" onclick="1', 'prompts' => [['text' => '示例']]]])];
    }
};
check($badCard->showcases($context, new AgentToolExecutor()) === [] && count($badCard->available($context, new AgentToolExecutor())) === 1,
    '无效能力卡片未丢弃或影响工具注册');
// 展示元数据不能影响真正的工具注册，也不能绕过权限和场景过滤。
$throwingCardRegistry = new class extends AgentToolRegistry {
    protected function modules(): array { return []; }
    protected function coreProviders(): array {
        return [new class extends FixtureProvider {
            public function showcase(): array { throw new RuntimeException('展示配置异常'); }
        }];
    }
};
check(count($throwingCardRegistry->available($context, new AgentToolExecutor())) === 1
    && $throwingCardRegistry->showcases($context, new AgentToolExecutor()) === [], '卡片异常影响工具注册');
foreach ([[], ['title' => '   ', 'prompts' => [['text' => '示例', 'tools' => ['fixture_write']]]],
    ['title' => '无依赖', 'prompts' => [['text' => '示例', 'tools' => []]]],
    ['title' => '缺少依赖', 'prompts' => [['text' => '示例']]]] as $card) {
    $registry = new class($card) extends AgentToolRegistry {
        public function __construct(private array $card) {}
        protected function modules(): array { return []; }
        protected function coreProviders(): array {
            return [new \app\common\agent\AgentToolConfig(['tools' => [(new FixtureTool())->definition()], 'showcase' => $this->card])];
        }
    };
    check(count($registry->available($context, new AgentToolExecutor())) === 1, '无效展示配置破坏工具注册');
    check($registry->showcases($context, new AgentToolExecutor(), 'other.scene') === [], '无依赖卡片绕过场景过滤');
    check($registry->showcases($context, new AgentToolExecutor()) === [], '无效或空依赖卡片仍展示');
}
$auth->allowed = false;
check($coreRegistry->showcases($context, new AgentToolExecutor()) === [], '无任何工具权限时仍展示功能');
$auth->allowed = true;

check(!str_contains(file_get_contents(dirname(__DIR__, 2) . '/app/common/agent/AdminAgent.php'), 'cms_'),
    '后台通用 Agent 仍写死 CMS 工具或业务指令');
$cmsResident = (require dirname(__DIR__, 2) . '/modules/cms/agent/config.php')['resident_tools'];
$cmsDefinitions = $plugins->available($context, $pluginExecutor, 'admin', ['cms']);
check(array_diff($cmsResident, array_column(array_filter($cmsDefinitions, fn ($tool) => $tool['resident']), 'name')) === [],
    'CMS 插件常驻声明没有通过注册中心传递');
$disabledCmsRegistry = new class extends AgentToolRegistry {
    protected function modules(): array { return [['name' => 'cms', 'state' => 0]]; }
    protected function coreProviders(): array { return []; }
};
check($disabledCmsRegistry->available($context, $pluginExecutor) === [], '停用插件仍提供常驻工具');

// 使用真实 CMS 配置与本地插件发现链路，验证目录里的工具确实注册并按权限返回。
$auth->menus = [['title' => 'badoucms', 'name' => 'cms', 'type' => 0, 'ismenu' => 1, 'children' => [
    ['title' => '内容管理', 'name' => 'cms.content', 'type' => 1, 'ismenu' => 1],
    ['title' => '栏目管理', 'name' => 'cms.content_sort', 'type' => 1, 'ismenu' => 1],
    ['title' => '发布', 'name' => 'cms.content/multi', 'type' => 2, 'ismenu' => 0],
    ['title' => '其他插件', 'name' => 'cms_other.content', 'type' => 1, 'ismenu' => 1],
]]];
$capabilityTool = array_values(array_filter($core, fn ($tool) => $tool['name'] === 'module_capabilities'))[0];
$capabilities = (new AgentToolExecutor())->execute($capabilityTool, $context, ['name' => 'CMS']);
check(array_column($capabilities['backend_features'], 'name') === ['cms.content', 'cms.content_sort'], '插件菜单范围或按钮过滤错误');
$cmsCapabilities = array_column($capabilities['agent_tools'], null, 'name');
check(count($cmsCapabilities) === 95 && isset($cmsCapabilities['cms_article_publish'], $cmsCapabilities['cms_category_create'],
    $cmsCapabilities['cms_category_read'], $cmsCapabilities['cms_category_update'],
    $cmsCapabilities['cms_site_update'], $cmsCapabilities['cms_company_create'], $cmsCapabilities['cms_form_field_create'],
    $cmsCapabilities['cms_language_copy']), '真实 CMS 注册链路缺失工具');
check($cmsCapabilities['cms_article_publish']['requires_approval'] === true, '发布审批信息丢失');
check(!isset($capabilities['agent_tools'][0]['handler']), '功能说明泄露内部执行定义');
$auth->denied = ['cms.content/edit', 'cms.content/multi'];
$auth->menus = [];
$capabilities = (new AgentToolExecutor())->execute($capabilityTool, $context, ['name' => 'cms']);
check($capabilities['backend_features'] === [] && count($capabilities['agent_tools']) === 91
    && !in_array('cms_article_summarize', array_column($capabilities['agent_tools'], 'name'), true)
    && !in_array('cms_article_publish', array_column($capabilities['agent_tools'], 'name'), true), '插件说明泄露无权限功能');
$auth->denied = [];
try {
    (new AgentToolExecutor())->execute($capabilityTool, $context, ['name' => '../cms']);
    throw new RuntimeException('插件名称未校验');
} catch (DomainException $e) {}

$legacy = (new FixtureTool())->definition();
$legacy['approval'] = 'confirm';
$legacy['properties'][] = ['name' => 'confirm', 'type' => 'boolean', 'required' => true];
$adapted = (new NeuronToolAdapter(new AgentToolExecutor()))->adapt($legacy, $context);
$adapted->setInputs(['id' => 2, 'confirm' => false]);
check(!$adapted->requiresApproval(), '旧工具预览不应审批');
$adapted->setInputs(['id' => 2, 'confirm' => true]);
check($adapted->requiresApproval(), '模型 confirm=true 不应绕过人工审批');

FixtureAgent::$pdo = new PDO('sqlite::memory:');
FixtureAgent::$pdo->exec('CREATE TABLE workflow_store ("partition" VARCHAR(510), "key" VARCHAR(510), value TEXT, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY ("partition", "key"))');
FixtureAgent::$pdo->exec('CREATE TABLE messages (id INTEGER PRIMARY KEY AUTOINCREMENT, thread_id TEXT, message_id TEXT, role TEXT, content TEXT, meta TEXT, archived_at DATETIME, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, UNIQUE(thread_id,message_id))');

class CapabilityFixtureAgent extends \app\common\agent\AdminAgent
{
    public function __construct(AgentContext $context, private AIProviderInterface $fixtureModel) { parent::__construct($context); }
    protected function provider(): AIProviderInterface { return $this->fixtureModel; }
    protected function persistence(): PersistenceInterface { return new DatabasePersistence(FixtureAgent::$pdo, 'workflow_store'); }
    protected function messageStore(): MessageStoreInterface { return new SQLMessageStore(FixtureAgent::$pdo, 'messages'); }
}
$overviewCall = ['role' => 'assistant', 'content' => null, 'tool_calls' => [[
    'id' => 'capability-1', 'type' => 'function', 'function' => ['name' => 'module_capabilities', 'arguments' => '{"name":"cms"}'],
]]];
$http = new FixtureHttp([$overviewCall, ['role' => 'assistant', 'content' => 'CMS 已接入文章查询、读取、摘要和发布。']]);
$agent = new CapabilityFixtureAgent(new AgentContext($auth, 1, 0, 200030), new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http));
$overviewService = new AgentService();
$overviewEvents = [];
$reply = $overviewService->ask($agent, 'CMS 插件当中有哪些功能？', static function ($event, $data) use (&$overviewEvents) { $overviewEvents[] = [$event, $data]; });
check(in_array('module_capabilities', $http->offeredTools[0], true), '插件功能说明未常驻加载');
check(count($http->offeredTools) === 2 && $reply['tools'] === ['查看插件功能'], '插件说明仍依赖反复检索');
check($http->modes === ['stream', 'stream'], '默认工具对话没有请求模型流式输出');
$overviewDeltas = array_values(array_filter($overviewEvents, fn ($event) => $event[0] === 'delta'));
check(count($overviewDeltas) === 2 && implode('', array_column(array_column($overviewDeltas, 1), 'text')) === $reply['answer'], '工具完成后的文字未分段实时推送');
$overviewService->acknowledge($agent, $reply);

// 默认普通文字也必须分段输出；兼容模式仍可显式关闭流式请求。
foreach ([true, false] as $streaming) {
    \think\facade\Config::set(['stream_tools' => $streaming], 'agent');
    $http = new FixtureHttp([['role' => 'assistant', 'content' => '这是普通文字回答']]);
    $agent = new FixtureAgent(new AgentContext($auth, 1, 0, $streaming ? 200034 : 200035),
        new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http));
    $events = [];
    $reply = $overviewService->ask($agent, '普通文字问题', static function ($event, $data) use (&$events) { $events[] = [$event, $data]; });
    check($http->modes === [$streaming ? 'stream' : 'chat'] && $reply['answer'] === '这是普通文字回答', '文字输出模式没有遵循配置');
    if ($streaming) {
        $deltas = array_values(array_filter($events, fn ($event) => $event[0] === 'delta'));
        check(count($deltas) === 2 && $deltas[0][1]['text'] === '这是' && $deltas[1][1]['text'] === '普通文字回答', '普通文字未按模型片段推送');
    }
    $overviewService->acknowledge($agent, $reply);
}
\think\facade\Config::set(['stream_tools' => true], 'agent');

// 连续两轮重新创建后台助手，栏目查询都必须常驻，不依赖上轮检索记录。
class CmsQueryFixtureAgent extends CapabilityFixtureAgent
{
    public function __construct(AgentContext $context, AIProviderInterface $fixtureModel, private array $fixtureTools)
    { parent::__construct($context, $fixtureModel); }
    public function toolDefinitions(): array { return $this->fixtureTools; }
}
$categoryExecutions = 0;
$categoryDefinitions = array_values(array_filter($plugins->available($context, $pluginExecutor), fn ($tool) => $tool['name'] === 'cms_category_search'));
foreach ($categoryDefinitions as &$definition) {
    $definition['module'] = 'core';
    $definition['handler'] = function ($context, $input) use (&$categoryExecutions) { $categoryExecutions++; return ['scode' => '1', 'name' => '新闻中心']; };
}
unset($definition);
$categoryCall = ['role' => 'assistant', 'content' => null, 'tool_calls' => [[
    'id' => 'category-1', 'type' => 'function', 'function' => ['name' => 'cms_category_search', 'arguments' => '{"keyword":"新闻中心"}'],
]]];
$categoryContext = new AgentContext($auth, 1, 0, 200032);
for ($turn = 0; $turn < 2; $turn++) {
    $http = new FixtureHttp([$categoryCall, ['role' => 'assistant', 'content' => '找到新闻中心']]);
    $agent = new CmsQueryFixtureAgent($categoryContext, new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http), $categoryDefinitions);
    $reply = $overviewService->ask($agent, '查询新闻中心', $turn === 0 ? null : static function () {});
    check(in_array('cms_category_search', $http->offeredTools[0], true) && $reply['answer'] === '找到新闻中心', '新一轮栏目查询未常驻');
    if ($turn === 1) check($reply['tools'] === ['查询栏目'], '流式栏目查询未记录工具执行');
    $overviewService->acknowledge($agent, $reply);
}
check($categoryExecutions === 2, '连续两轮栏目查询未执行');

// 写文章流程直接提供栏目查询和草稿新增 schema，完整参数通过后仍须审批。
$articleFlowDefinitions = array_values(array_filter($plugins->available($context, $pluginExecutor), fn ($tool) => in_array($tool['name'], ['cms_category_search', 'cms_article_create'], true)));
$articleFlowWrites = 0;
foreach ($articleFlowDefinitions as &$definition) {
    $definition['module'] = 'core';
    $definition['handler'] = $definition['name'] === 'cms_category_search'
        ? static fn () => ['language' => 'cn', 'categories' => [['scode' => '1', 'name' => '新闻中心']]]
        : function () use (&$articleFlowWrites) { $articleFlowWrites++; return ['created' => true]; };
}
unset($definition);
$draftCall = ['role' => 'assistant', 'content' => null, 'tool_calls' => [[
    'id' => 'draft-1', 'type' => 'function', 'function' => ['name' => 'cms_article_create', 'arguments' => json_encode([
        'language' => 'cn', 'scode' => '1', 'title' => 'badoucms 介绍', 'content' => '<p>内容管理系统介绍。</p>',
    ], JSON_UNESCAPED_UNICODE)],
]]];
$flowContext = new AgentContext($auth, 1, 0, 200033);
$http = new FixtureHttp([$categoryCall, $draftCall]);
$agent = new CmsQueryFixtureAgent($flowContext, new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http), $articleFlowDefinitions);
$draftReply = $overviewService->ask($agent, '写一篇介绍放在新闻中心', static function () {});
check(in_array('cms_article_create', $http->offeredTools[0], true) && count($http->offeredTools) === 2, '写文章流程仍依赖额外检索');
check($draftReply['approval']['actions'][0]['name'] === 'cms_article_create' && $articleFlowWrites === 0, '草稿创建绕过审批');
check($http->modes === ['stream', 'stream'] && $draftReply['tools'] === ['查询栏目'], '默认流式模式未保留工具进度或审批');
unset($agent);
$agent = new CmsQueryFixtureAgent($flowContext, model([['role' => 'assistant', 'content' => '草稿保存完成']]), $articleFlowDefinitions);
$draftReply = $overviewService->resume($agent, $draftReply['approval']['run_id'], $draftReply['approval']['attempt'], 'approve');
check($articleFlowWrites === 1 && $draftReply['answer'] === '草稿保存完成', '审批后草稿未恰好执行一次');
$overviewService->acknowledge($agent, $draftReply);

class CmsContentFixtureAgent extends \modules\cms\agent\ContentAgent
{
    public function __construct(AgentContext $context, private AIProviderInterface $fixtureModel, private array $fixtureTools)
    { parent::__construct($context); }
    protected function provider(): AIProviderInterface { return $this->fixtureModel; }
    protected function persistence(): PersistenceInterface { return new DatabasePersistence(FixtureAgent::$pdo, 'workflow_store'); }
    protected function messageStore(): MessageStoreInterface { return new SQLMessageStore(FixtureAgent::$pdo, 'messages'); }
    public function toolDefinitions(): array { return $this->fixtureTools; }
}

// 用户在下一轮确认新增栏目时，后台与 CMS 助手必须带上新增 schema，仍由原审批状态执行。
$categoryFlowDefinitions = array_values(array_filter($plugins->available($context, $pluginExecutor),
    fn ($tool) => in_array($tool['name'], ['cms_category_search', 'cms_category_create', 'cms_article_create'], true)));
$categoryFlowWrites = [];
foreach ($categoryFlowDefinitions as &$definition) {
    $definition['module'] = 'core';
    $definition['handler'] = $definition['name'] === 'cms_category_search'
        ? static fn () => ['language' => 'cn', 'categories' => [], 'models' => [['mcode' => '1', 'name' => '文章', 'type' => 2]]]
        : function ($context, $input) use (&$categoryFlowWrites) {
            $categoryFlowWrites[] = $input;
            return ['created' => true, 'scode' => '20', 'language' => 'cn', 'name' => $input['name']];
        };
}
unset($definition);
$categoryFlowIndex = 0;
foreach ([CmsQueryFixtureAgent::class, CmsContentFixtureAgent::class] as $agentClass) {
    foreach (['approve', 'reject'] as $decision) {
        $categoryFlowContext = new AgentContext($auth, 1, 0, 200080 + $categoryFlowIndex++, autoApprove: true);
        $before = count($categoryFlowWrites);
        $agent = new $agentClass($categoryFlowContext, model([$categoryCall,
            ['role' => 'assistant', 'content' => '未找到该栏目，可以新增。']]), $categoryFlowDefinitions);
        $reply = $overviewService->ask($agent, '准备添加文档栏目');
        $overviewService->acknowledge($agent, $reply);
        unset($agent);

        $categoryCreateCall = ['role' => 'assistant', 'content' => null, 'tool_calls' => [[
            'id' => 'category-create-' . $categoryFlowIndex, 'type' => 'function', 'function' => [
                'name' => 'cms_category_create', 'arguments' => json_encode([
                    'language' => 'cn', 'name' => '文档栏目', 'mcode' => '1', 'pcode' => '0',
                    'aucode' => 'document_category', 'filename' => 'document-category',
                ], JSON_UNESCAPED_UNICODE),
            ],
        ]]];
        $http = new FixtureHttp([$categoryCall, $categoryCreateCall]);
        $agent = new $agentClass($categoryFlowContext,
            new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http), $categoryFlowDefinitions);
        $reply = $overviewService->ask($agent, '确认，先添加栏目', static function () {});
        check(in_array('cms_category_create', $http->offeredTools[0], true)
            && in_array('cms_article_create', $http->offeredTools[0], true), '栏目或后续文章新增工具未常驻加载');
        check($http->toolSchemas[0]['cms_category_create']['parameters']['required'] === ['language', 'name', 'mcode', 'aucode', 'filename'], '栏目新增的完整参数 schema 未发送');
        check($reply['approval']['actions'][0]['name'] === 'cms_category_create'
            && count($categoryFlowWrites) === $before, '文字确认或自动批准设置绕过了栏目写入审批');
        check($reply['tools'] === ['查询栏目'] && $http->modes === ['stream', 'stream'], '查询后新增流程依赖检索或丢失流式进度');
        unset($agent);

        $agent = new $agentClass($categoryFlowContext, model([['role' => 'assistant',
            'content' => $decision === 'approve' ? '栏目已新增。' : '已取消新增栏目。']]), $categoryFlowDefinitions);
        $approval = $reply['approval'];
        $reply = $overviewService->resume($agent, $approval['run_id'], $approval['attempt'], $decision);
        check(count($categoryFlowWrites) === $before + ($decision === 'approve' ? 1 : 0), '栏目审批写入次数不正确');
        if ($decision === 'approve') {
            $saved = end($categoryFlowWrites);
            check(array_intersect_key($saved, array_flip(['language', 'name', 'mcode', 'pcode', 'aucode', 'filename']))
                === ['language' => 'cn', 'name' => '文档栏目', 'mcode' => '1', 'aucode' => 'document_category', 'filename' => 'document-category', 'pcode' => '0'], '审批恢复改变了栏目参数');
        }
        try {
            $overviewService->resume($agent, $approval['run_id'], $approval['attempt'], $decision);
            throw new RuntimeException('同一栏目审批被重复执行');
        } catch (DomainException $error) {}
        $overviewService->acknowledge($agent, $reply);
    }
}
$auth->denied = ['cms.content_sort/add'];
check(!in_array('cms_category_create', array_column($plugins->available($context, $pluginExecutor), 'name'), true), '无栏目新增权限时仍加载了新增工具');
$auth->denied = [];

// 已有栏目编辑在两种助手中常驻加载，审批跨请求恢复仅保存指定字段。
$editDefinitions = array_values(array_filter($plugins->available($context, $pluginExecutor),
    fn ($tool) => in_array($tool['name'], ['cms_category_read', 'cms_category_update'], true)));
$editWrites = [];
$editSnapshot = str_repeat('e', 64);
foreach ($editDefinitions as &$definition) {
    $definition['module'] = 'core';
    if ($definition['name'] === 'cms_category_read') {
        $definition['handler'] = static fn () => ['language' => 'cn', 'scode' => '23', 'values' => ['name' => '服务解决方案'], 'snapshot' => $editSnapshot];
    } else {
        $definition['approval_validate'] = static function ($context, $input) use ($editSnapshot): void {
            if (($input['snapshot'] ?? '') !== $editSnapshot) throw new DomainException('栏目已变化');
        };
        $definition['approval_display'] = static fn () => ['title' => '修改栏目：服务解决方案',
            'changes' => [['label' => '通用编码', 'before' => '', 'after' => 'geo_service_solutions']]];
        $definition['handler'] = static function ($context, $input) use (&$editWrites): array {
            $editWrites[] = $input;
            return ['updated' => true, 'scode' => '23', 'language' => 'cn'];
        };
    }
}
unset($definition);
$editIndex = 0;
foreach ([CmsQueryFixtureAgent::class, CmsContentFixtureAgent::class] as $agentClass) {
    foreach (['approve', 'reject'] as $decision) {
        $editContext = new AgentContext($auth, 1, 0, 200095 + $editIndex++, autoApprove: true);
        $input = ['language' => 'cn', 'scode' => '23', 'snapshot' => $editSnapshot, 'aucode' => 'geo_service_solutions', 'filename' => 'service-solutions'];
        $calls = [];
        foreach (['cms_category_read' => ['scode' => '23'], 'cms_category_update' => $input] as $name => $arguments) {
            $calls[] = ['role' => 'assistant', 'content' => null, 'tool_calls' => [[
                'id' => $name . '-' . $editIndex, 'type' => 'function', 'function' => ['name' => $name, 'arguments' => json_encode($arguments)],
            ]]];
        }
        $http = new FixtureHttp($calls);
        $before = count($editWrites);
        $agent = new $agentClass($editContext, new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http), $editDefinitions);
        $reply = $overviewService->ask($agent, '补齐服务解决方案的英文通用编码和 URL', static function () {});
        check(in_array('cms_category_read', $http->offeredTools[0], true) && in_array('cms_category_update', $http->offeredTools[0], true), '栏目详情与修改工具没有常驻加载');
        check($http->toolSchemas[0]['cms_category_update']['parameters']['required'] === ['language', 'scode', 'snapshot'], '栏目修改缺少目标和版本参数');
        check(array_diff(array_keys(\modules\cms\agent\service\CategoryAgentService::EDITABLE_FIELDS),
            array_keys($http->toolSchemas[0]['cms_category_update']['parameters']['properties'])) === [], 'SDK 未提供所有栏目字段');
        check(count($editWrites) === $before && $reply['approval']['actions'][0]['name'] === 'cms_category_update', '栏目编辑绕过审批');
        check($reply['approval']['actions'][0]['display']['changes'][0]['after'] === 'geo_service_solutions', '栏目修改审批丢失可读对照');
        unset($agent);
        $agent = new $agentClass($editContext, model([['role' => 'assistant', 'content' => '已处理栏目修改。']]), $editDefinitions);
        $approval = $reply['approval'];
        $reply = $overviewService->resume($agent, $approval['run_id'], $approval['attempt'], $decision);
        check(count($editWrites) === $before + ($decision === 'approve' ? 1 : 0), '栏目编辑批准或取消的写入次数错误');
        if ($decision === 'approve') check(array_filter(end($editWrites), static fn ($value) => $value !== null) == $input, '栏目编辑恢复改变了指定字段');
        $overviewService->acknowledge($agent, $reply);
    }
}

// 后台和 CMS 助手在下一轮直接调用复制时仍须加载工具，且审批前不得写入。
$languageNames = ['cms_language_list', 'cms_language_copy_preview', 'cms_language_copy'];
$languageDefinitions = array_values(array_filter($plugins->available($context, $pluginExecutor), fn ($tool) => in_array($tool['name'], $languageNames, true)));
$copySnapshot = hash('sha256', '语言复制测试快照');
$copyWrites = 0;
foreach ($languageDefinitions as &$definition) {
    $definition['module'] = 'core';
    if ($definition['name'] === 'cms_language_copy') $definition['approval_display'] = static fn () => ['type' => 'language_copy', 'title' => '复制栏目及内容', 'source' => '中文', 'target' => 'English'];
    $definition['handler'] = match ($definition['name']) {
        'cms_language_list' => static fn () => ['languages' => [['acode' => 'cn'], ['acode' => 'en']]],
        'cms_language_copy_preview' => static fn () => ['snapshot' => $copySnapshot, 'categories' => 1, 'contents' => 1],
        'cms_language_copy' => function ($context, $input) use (&$copyWrites, $copySnapshot) {
            check($input['snapshot'] === $copySnapshot && $input['source'] === 'cn' && $input['target'] === 'en', '复制审批丢失预览参数');
            $copyWrites++;
            return ['copied' => true];
        },
    };
}
unset($definition);
$languageCalls = [];
foreach ($languageNames as $name) {
    $input = $name === 'cms_language_list' ? [] : ['source' => 'cn', 'target' => 'en'];
    if ($name === 'cms_language_copy') $input['snapshot'] = $copySnapshot;
    $languageCalls[$name] = ['role' => 'assistant', 'content' => null, 'tool_calls' => [[
        'id' => $name . '-1', 'type' => 'function', 'function' => ['name' => $name, 'arguments' => json_encode((object)$input)],
    ]]];
}
foreach ([CmsQueryFixtureAgent::class, CmsContentFixtureAgent::class] as $index => $fixtureClass) {
    $copyContext = new AgentContext($auth, 1, 0, 200034 + $index);
    $http = new FixtureHttp([$languageCalls['cms_language_list'], $languageCalls['cms_language_copy_preview'], ['role' => 'assistant', 'content' => '预览完成，等待复制指令']]);
    $agent = new $fixtureClass($copyContext, new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http), $languageDefinitions);
    $previewReply = $overviewService->ask($agent, '先预览中文复制到英文', static function () {});
    check(array_diff($languageNames, $http->offeredTools[0]) === [] && count($http->offeredTools) === 3, '语言复制流程首轮未直接加载完整工具');
    $overviewService->acknowledge($agent, $previewReply);
    unset($agent);
    $http = new FixtureHttp([$languageCalls['cms_language_copy']]);
    $agent = new $fixtureClass($copyContext, new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http), $languageDefinitions);
    $copyReply = $overviewService->ask($agent, '先复制到英文，成功后再翻译', static function () {});
    check(array_diff($languageNames, $http->offeredTools[0]) === [], '新一轮未重新加载语言复制工具');
    check($copyReply['approval']['actions'][0]['name'] === 'cms_language_copy' && $copyWrites === $index, '语言复制未暂停等待审批');
    unset($agent);
    $agent = new $fixtureClass($copyContext, model([['role' => 'assistant', 'content' => '复制完成']]), $languageDefinitions);
    $copyReply = $overviewService->resume($agent, $copyReply['approval']['run_id'], $copyReply['approval']['attempt'], 'approve');
    check($copyWrites === $index + 1 && $copyReply['answer'] === '复制完成', '批准后语言复制未恰好执行一次');
    $overviewService->acknowledge($agent, $copyReply);
}

// 常驻只是加载策略，仍须经过真实注册中心的权限过滤。
$auth->denied = ['cms.content/copy'];
$deniedLanguageDefinitions = array_values(array_filter($plugins->available($context, $pluginExecutor), fn ($tool) => in_array($tool['name'], $languageNames, true)));
foreach ([CmsQueryFixtureAgent::class, CmsContentFixtureAgent::class] as $index => $fixtureClass) {
    $http = new FixtureHttp([['role' => 'assistant', 'content' => '无复制权限']]);
    $agent = new $fixtureClass(new AgentContext($auth, 1, 0, 200036 + $index), new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http), $deniedLanguageDefinitions);
    $reply = $overviewService->ask($agent, '复制到英文');
    check(!in_array('cms_language_copy', $http->offeredTools[0], true) && in_array('cms_language_copy_preview', $http->offeredTools[0], true), '常驻语言复制工具绕过权限');
    $overviewService->acknowledge($agent, $reply);
}
$auth->denied = [];

// 同一原生运行中，复制批准后继续生成译文，并再次暂停等待译文审批。
$translationNames = ['cms_language_translation_preview', 'cms_language_translate'];
$translationDefinitions = array_values(array_filter($plugins->available($context, $pluginExecutor), fn ($tool) => in_array($tool['name'], $translationNames, true)));
$translationSnapshot = hash('sha256', '待翻译记录');
$translationWrites = 0;
foreach ($translationDefinitions as &$definition) {
    $definition['module'] = 'core';
    if ($definition['name'] === 'cms_language_translation_preview') {
        $definition['handler'] = static fn () => ['snapshot' => $translationSnapshot, 'items' => [['id' => 12, 'fields' => ['name' => '新闻']]], 'has_more' => false];
    } else {
        $definition['approval_display'] = static fn ($context, $input) => ['type' => 'language_translation', 'title' => '翻译栏目',
            'source' => '中文', 'target' => '英文', 'items' => [['name' => '新闻', 'fields' => [['label' => '栏目名称', 'before' => '新闻', 'after' => $input['items'][0]['fields']['name']]]]]];
        $definition['approval_validate'] = static function ($context, $input) use ($translationSnapshot): void {
            if (($input['snapshot'] ?? '') !== $translationSnapshot) throw new \app\common\agent\ApprovalExpiredException('请重新读取本轮翻译预览');
        };
        $display = $definition['approval_display'];
        $validate = $definition['approval_validate'];
        $definition['approval_display'] = static function ($context, $input) use ($display, $validate): array {
            $validate($context, $input);
            return $display($context, $input);
        };
        $definition['handler'] = function ($context, $input) use (&$translationWrites, $translationSnapshot) {
            check($input['snapshot'] === $translationSnapshot && $input['items'] === [['id' => 12, 'fields' => ['name' => 'News']]], '译文保存参数丢失');
            $translationWrites++;
            return ['translated' => true, 'saved' => 1, 'has_more' => false];
        };
    }
}
unset($definition);
$translationCall = ['role' => 'assistant', 'content' => null, 'tool_calls' => [[
    'id' => 'translation-preview-1', 'type' => 'function', 'function' => ['name' => 'cms_language_translation_preview', 'arguments' => '{"source":"cn","target":"en","kind":"categories"}'],
]]];
$translationSaveCall = ['role' => 'assistant', 'content' => null, 'tool_calls' => [[
    'id' => 'translation-save-1', 'type' => 'function', 'function' => ['name' => 'cms_language_translate', 'arguments' => json_encode([
        'source' => 'cn', 'target' => 'en', 'kind' => 'categories', 'snapshot' => $translationSnapshot, 'items' => [['id' => 12, 'fields' => ['name' => 'News']]],
    ])],
]]];
foreach ([CmsQueryFixtureAgent::class, CmsContentFixtureAgent::class] as $index => $fixtureClass) {
    $translationContext = new AgentContext($auth, 1, 0, 200038 + $index);
    $flowDefinitions = array_merge($languageDefinitions, $translationDefinitions);
    $http = new FixtureHttp([$languageCalls['cms_language_list'], $languageCalls['cms_language_copy_preview'], $languageCalls['cms_language_copy']]);
    $agent = new $fixtureClass($translationContext, new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http), $flowDefinitions);
    $reply = $overviewService->ask($agent, '先复制中文栏目到英文再翻译', static function () {});
    check(array_diff(array_merge($languageNames, $translationNames), $http->offeredTools[0]) === [], '翻译流程工具未常驻加载');
    check($http->toolSchemas[0]['cms_language_translate']['parameters']['properties']['items']['items']['properties']['fields']['properties']['name']['type'] === 'string', '译文数组缺少完整嵌套 schema，模型网关无法接收');
    check($reply['approval']['actions'][0]['display']['title'] === '复制栏目及内容' && $translationWrites === $index, '复制审批摘要未提供或过早保存译文');
    $beforeCopyWrites = $copyWrites;
    unset($agent);
    $agent = new $fixtureClass($translationContext, model([$translationCall, $translationSaveCall]), $flowDefinitions);
    $reply = $overviewService->resume($agent, $reply['approval']['run_id'], $reply['approval']['attempt'], 'approve', static function () {});
    check($copyWrites === $beforeCopyWrites + 1 && $translationWrites === $index, '复制审批未执行一次或译文绕过独立审批');
    check($reply['approval']['actions'][0]['name'] === 'cms_language_translate'
        && $reply['approval']['actions'][0]['display']['items'][0]['fields'][0]['after'] === 'News', '复制后没有继续生成译文审批');
    try {
        $overviewService->resume($agent, $reply['approval']['run_id'], $reply['approval']['attempt'], 'refresh');
        throw new RuntimeException('有效审批被当成过期请求重新生成');
    } catch (DomainException $e) {}
    unset($agent);
    $agent = new $fixtureClass($translationContext, model([['role' => 'assistant', 'content' => '栏目复制及翻译已完成']]), $flowDefinitions);
    $reply = $overviewService->resume($agent, $reply['approval']['run_id'], $reply['approval']['attempt'], 'approve');
    check($translationWrites === $index + 1 && $reply['approval'] === null && $reply['answer'] === '栏目复制及翻译已完成', '译文审批后未正确完成运行');
    $overviewService->acknowledge($agent, $reply);
}

// 复现模型直接复用旧预览：失败交回模型，重新读取后才生成可批准的卡片。
$expiredSaveCall = $translationSaveCall;
$expiredInput = json_decode($expiredSaveCall['tool_calls'][0]['function']['arguments'], true);
$expiredInput['snapshot'] = hash('sha256', '旧翻译预览');
$expiredSaveCall['tool_calls'][0]['id'] = 'expired-save';
$expiredSaveCall['tool_calls'][0]['function']['arguments'] = json_encode($expiredInput);
$freshSaveCall = $translationSaveCall;
$freshSaveCall['tool_calls'][0]['id'] = 'fresh-save';
$recoveryContext = new AgentContext($auth, 1, 0, 200040);
$recoveryEvents = [];
$agent = new CmsQueryFixtureAgent($recoveryContext, model([$expiredSaveCall, $translationCall, $freshSaveCall]), $translationDefinitions);
$beforeTranslationWrites = $translationWrites;
$recovered = $overviewService->ask($agent, '重翻译遗漏记录', static function ($event, $data) use (&$recoveryEvents) { $recoveryEvents[] = [$event, $data]; });
check($translationWrites === $beforeTranslationWrites && $recovered['approval']['actions'][0]['inputs']['snapshot'] === $translationSnapshot
    && !isset($recovered['approval']['actions'][0]['display']['error']), '旧预览没有纠正为可批准的新卡片或提前写入');
check(count(array_filter($recoveryEvents, static fn ($event) => $event[0] === 'tool_error')) === 1, '旧快照错误没有交回模型');
$recovered = $overviewService->resume(new CmsQueryFixtureAgent($recoveryContext, model([['role' => 'assistant', 'content' => '新译文已保存']]), $translationDefinitions),
    $recovered['approval']['run_id'], $recovered['approval']['attempt'], 'approve');
check($translationWrites === $beforeTranslationWrites + 1, '新审批没有恰好保存一次');
$overviewService->acknowledge(new CmsQueryFixtureAgent($recoveryContext, model([]), $translationDefinitions), $recovered);

// 旧版本已经暂停的失效卡，冷进程恢复后可重新生成，但不能执行旧译文。
$legacyDefinitions = $translationDefinitions;
foreach ($legacyDefinitions as &$definition) {
    if ($definition['name'] !== 'cms_language_translate') continue;
    unset($definition['approval_validate']);
    $definition['approval_display'] = static fn () => ['type' => 'language_translation', 'title' => '旧译文'];
}
unset($definition);
$legacyContext = new AgentContext($auth, 1, 0, 200041);
$legacyAgent = new CmsQueryFixtureAgent($legacyContext, model([$expiredSaveCall]), $legacyDefinitions);
$legacyReply = $overviewService->ask($legacyAgent, '重翻译遗漏记录');
$oldApproval = $legacyReply['approval'];
$agent = new CmsQueryFixtureAgent($legacyContext, model([$translationCall, $freshSaveCall]), $translationDefinitions);
$restoredApproval = $overviewService->approval($agent);
check($restoredApproval['actions'][0]['display']['can_refresh'] === true, '旧失效审批没有重新生成入口');
$beforeTranslationWrites = $translationWrites;
$renewed = $overviewService->resume($agent, $oldApproval['run_id'], $oldApproval['attempt'], 'refresh', static function () {});
check($translationWrites === $beforeTranslationWrites && $renewed['approval']['actions'][0]['inputs']['snapshot'] === $translationSnapshot
    && !isset($renewed['approval']['actions'][0]['display']['error']), '重新生成执行了旧保存或新卡仍不可批准');
try {
    $overviewService->resume(new CmsQueryFixtureAgent($legacyContext, model([]), $translationDefinitions), $oldApproval['run_id'], $oldApproval['attempt'], 'refresh');
    throw new RuntimeException('重复刷新旧状态版本仍被接受');
} catch (DomainException $e) {}
$renewed = $overviewService->resume(new CmsQueryFixtureAgent($legacyContext, model([['role' => 'assistant', 'content' => '重新生成的译文已保存']]), $translationDefinitions),
    $renewed['approval']['run_id'], $renewed['approval']['attempt'], 'approve');
check($translationWrites === $beforeTranslationWrites + 1, '新卡批准后未恰好保存一次');
$overviewService->acknowledge(new CmsQueryFixtureAgent($legacyContext, model([]), $translationDefinitions), $renewed);

// 预检失败后数据恢复，也不能让这次调用跳过审批执行。
$gateValid = false;
$gateWrites = 0;
$gateDefinition = (new FixtureTool())->definition() + ['module' => 'core'];
$gateDefinition['approval_validate'] = static function () use (&$gateValid): void {
    if (!$gateValid) throw new \app\common\agent\ApprovalExpiredException('旧版本');
};
$gateDefinition['handler'] = static function () use (&$gateWrites): array { $gateWrites++; return []; };
$gateTool = (new NeuronToolAdapter(new AgentToolExecutor()))->adapt($gateDefinition, $context);
$gateTool->setInputs(['id' => 2]);
check(!$gateTool->requiresApproval(), '失效输入仍要求人工批准');
$gateValid = true;
try { $gateTool->execute(); throw new RuntimeException('预检失败后绕过审批写入'); }
catch (\app\common\agent\ApprovalExpiredException $e) {}
check($gateWrites === 0, '预检与执行之间的状态变化绕过了审批');

$call = ['role' => 'assistant', 'content' => null, 'tool_calls' => [['id' => 'call-1', 'type' => 'function', 'function' => ['name' => 'fixture_write', 'arguments' => '{"id":2}']]]];

// 整项后台任务只中断一次；明确选择自动批准时直接入队，普通写工具仍保持审批。
$taskDefinitions = array_values(array_filter($plugins->available($context, $pluginExecutor), fn ($tool) => $tool['name'] === 'cms_language_task_start'));
$taskSubmissions = 0;
foreach ($taskDefinitions as &$definition) {
    $definition['module'] = 'core';
    $definition['approval_display'] = static fn () => ['type' => 'language_task', 'title' => '后台翻译', 'total' => 33];
    $definition['handler'] = function () use (&$taskSubmissions) { $taskSubmissions++; return ['submitted' => true, 'task' => ['id' => 8, 'status' => 'queued', 'total' => 33]]; };
}
unset($definition);
$taskCall = ['role' => 'assistant', 'content' => null, 'tool_calls' => [[
    'id' => 'whole-task-1', 'type' => 'function', 'function' => ['name' => 'cms_language_task_start', 'arguments' => json_encode(['source' => 'cn', 'target' => 'en', 'kind' => 'contents', 'snapshot' => hash('sha256', '任务预览')])],
]]];
$wholeContext = new AgentContext($auth, 1, 0, 200040);
$agent = new CmsQueryFixtureAgent($wholeContext, model([$taskCall]), $taskDefinitions);
$wholeReply = $overviewService->ask($agent, '一次处理所有内容');
check($wholeReply['approval']['actions'][0]['display']['total'] === 33 && $taskSubmissions === 0, '整项任务未等待初次审批');
$agent = new CmsQueryFixtureAgent($wholeContext, model([['role' => 'assistant', 'content' => '已提交后台任务，请查看进度']]), $taskDefinitions);
$wholeReply = $overviewService->resume($agent, $wholeReply['approval']['run_id'], $wholeReply['approval']['attempt'], 'approve');
check($taskSubmissions === 1 && $wholeReply['approval'] === null, '任务提交后仍要求逐批审批');
$overviewService->acknowledge($agent, $wholeReply);
$automaticContext = new AgentContext($auth, 1, 0, 200041, autoApprove: true);
$agent = new CmsQueryFixtureAgent($automaticContext, model([$taskCall, ['role' => 'assistant', 'content' => '后台任务已提交']]), $taskDefinitions);
$automaticReply = $overviewService->ask($agent, '自动处理全部内容');
check($taskSubmissions === 2 && $automaticReply['approval'] === null, '自动批准模式仍暂停整项任务');
$overviewService->acknowledge($agent, $automaticReply);

$service = new AgentService();
$agent = new FixtureAgent($context, model([$call]));
$reply = $service->ask($agent, '保存');
check($reply['approval'] !== null && FixtureTool::$executions === 0, '审批前执行了工具');
unset($agent);
$agent = new FixtureAgent($context, model([['role' => 'assistant', 'content' => '保存完成']]));
$pending = $service->approval($agent);
check($pending['run_id'] === $reply['approval']['run_id'], '重建 Agent 后审批丢失');
$completed = $service->resume($agent, $pending['run_id'], $pending['attempt'], 'approve');
check($completed['answer'] === '保存完成' && FixtureTool::$executions === 1, '批准后未正确执行');
try {
    $service->resume(new FixtureAgent($context, model([])), $pending['run_id'], $pending['attempt'], 'approve');
    throw new RuntimeException('重复审批未拒绝');
} catch (DomainException $e) {}
check(FixtureTool::$executions === 1, '重复审批重复执行');
$recovered = $service->recover(new FixtureAgent($context, model([])));
check($recovered['answer'] === '保存完成', '完成结果无法恢复');
$service->acknowledge($agent, $completed);
check($agent->inspect() === null, '完成状态未清理');

foreach (['reject', 'revoked', 'disabled'] as $case) {
    $context = new AgentContext($auth, 1, 0, random_int(10, 100000));
    FixtureAgent::$enabled = true;
    $auth->allowed = true;
    $reply = $service->ask(new FixtureAgent($context, model([$call])), '保存');
    $pending = $reply['approval'];
    if ($case === 'revoked') $auth->allowed = false;
    if ($case === 'disabled') FixtureAgent::$enabled = false;
    $agent = new FixtureAgent($context, model([['role' => 'assistant', 'content' => '操作取消']]));
    if ($case !== 'reject') {
        try { $service->resume($agent, $pending['run_id'], $pending['attempt'], 'approve'); throw new RuntimeException('权限变化后仍允许审批'); }
        catch (DomainException $e) {}
    }
    $reply = $service->resume($agent, $pending['run_id'], $pending['attempt'], 'reject');
    check($reply['approval'] === null && FixtureTool::$executions === 1, '取消或权限变化导致执行');
    $service->acknowledge($agent, $reply);
}

// 工具已执行但后续模型请求失败时，开启新轮次不能再次执行旧工具。
$auth->allowed = true;
FixtureAgent::$enabled = true;
$context = new AgentContext($auth, 1, 0, 200001);
$reply = $service->ask(new FixtureAgent($context, model([$call])), '保存');
$pending = $reply['approval'];
try {
    $service->resume(new FixtureAgent($context, model([])), $pending['run_id'], $pending['attempt'], 'approve');
    throw new RuntimeException('模型失败未传播');
} catch (RuntimeException $e) { check($e->getMessage() === '不应发生额外模型请求', '出现预期之外的异常'); }
$reply = $service->ask(new FixtureAgent($context, model([['role' => 'assistant', 'content' => '新问题回答']])), '新问题');
check($reply['answer'] === '新问题回答' && FixtureTool::$executions === 2, '失败恢复重放了旧工具');

// 使用默认流式模式，继续覆盖逐字输出和审批恢复。
$context = new AgentContext($auth, 1, 0, 200002);
$events = [];
$progress = function ($event, $data) use (&$events) { $events[] = [$event, $data]; };
$reply = $service->ask(new FixtureAgent($context, model([$call])), '流式保存', $progress);
check($reply['approval'] !== null && FixtureTool::$executions === 2, '流式审批前执行了工具');
$pending = $reply['approval'];
$agent = new FixtureAgent($context, model([['role' => 'assistant', 'content' => '流式完成']]));
$reply = $service->resume($agent, $pending['run_id'], $pending['attempt'], 'approve', $progress);
check($reply['answer'] === '流式完成' && FixtureTool::$executions === 3, '流式审批恢复失败');
check(in_array('tool_done', array_column($events, 0), true) && in_array('delta', array_column($events, 0), true), '原生流事件未转换');
$service->acknowledge($agent, $reply);

$auth->allowed = true;
$executor = new class extends AgentToolExecutor { protected function moduleEnabled(string $name): bool { return false; } };
try { $executor->execute((new FixtureTool())->definition() + ['module' => 'disabled'], $context, ['id' => 2]); throw new RuntimeException('执行时未核对插件状态'); }
catch (DomainException $e) {}
check(FixtureTool::$executions === 3, '停用插件仍执行');

// 真实使用公共中间件，验证“查找 -> 加载 schema -> 调用”的完整链路。
class SearchFixtureAgent extends BaseAgent
{
    public function __construct(AgentContext $context, private AIProviderInterface $fixtureModel, private array $fixtureTools, $progress = null)
    { parent::__construct($context, $progress); }
    protected function provider(): AIProviderInterface { $this->context->control?->check(true); return $this->fixtureModel; }
    protected function persistence(): PersistenceInterface { return new DatabasePersistence(FixtureAgent::$pdo, 'workflow_store'); }
    protected function messageStore(): MessageStoreInterface { return new SQLMessageStore(FixtureAgent::$pdo, 'messages'); }
    public function toolDefinitions(): array { return $this->fixtureTools; }
}

$definitions = [];
$searchExecutions = 0;
foreach ((new \app\common\agent\AgentToolConfig(require dirname(__DIR__, 2) . '/modules/cms/agent/article.php'))->agentTools() as $definition) {
    $definition['module'] = 'core';
    $definition['handler'] = function ($context, $input) use (&$searchExecutions) { $searchExecutions++; return ['total' => 7]; };
    $definitions[] = $definition;
}
$pool = array_map(fn ($tool) => (new NeuronToolAdapter(new AgentToolExecutor()))->adapt($tool, $context), $definitions);
$finder = new \app\common\agent\middleware\ToolSearchTool($pool);
foreach (['CMS 插件', '查询CMS文章', '文章总数', 'CMS_ARTICLE_SEARCH'] as $query) {
    $matches = $finder->search($query);
    check($matches !== [] && str_starts_with($matches[0]->getName(), 'cms_article_'), '中文或 CMS 缩写检索失败：' . $query);
}

check($finder->search('文章总数')[0]->getName() === 'cms_article_search', '文章数量查询定位到了错误的工具');

$searchCall = ['role' => 'assistant', 'content' => null, 'tool_calls' => [['id' => 'search-1', 'type' => 'function', 'function' => ['name' => 'tool_search', 'arguments' => '{"query":"查询CMS文章"}']]]];
$articleCall = ['role' => 'assistant', 'content' => null, 'tool_calls' => [['id' => 'article-1', 'type' => 'function', 'function' => ['name' => 'cms_article_search', 'arguments' => '{"keyword":""}']]]];
$http = new FixtureHttp([$searchCall, $articleCall, ['role' => 'assistant', 'content' => '共有 7 篇文章']]);
$context = new AgentContext($auth, 1, 0, 200010);
$agent = new SearchFixtureAgent($context, new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http), $definitions);
$reply = $service->ask($agent, '查询 CMS 文章');
check($reply['answer'] === '共有 7 篇文章' && $searchExecutions === 1, '发现工具后未执行');
check(!in_array('cms_article_search', $http->offeredTools[0], true) && in_array('cms_article_search', $http->offeredTools[1], true), '检索后工具 schema 未加载');
$service->acknowledge($agent, $reply);

$badArticleCall = $articleCall;
$badArticleCall['tool_calls'][0]['function']['arguments'] = '{"keyword":[]}';
$http = new FixtureHttp([$searchCall, $badArticleCall, ['role' => 'assistant', 'content' => '工具参数不正确']]);
$context = new AgentContext($auth, 1, 0, 200031);
$agent = new SearchFixtureAgent($context, new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http), $definitions);
$events = [];
$reply = $service->ask($agent, '检验工具参数错误显示', function ($event, $data) use (&$events) { $events[] = [$event, $data]; });
check(count(array_filter($events, fn ($event) => $event[0] === 'tool_error')) === 1, '原生 ToolOutput 参数错误被显示为成功');
check($reply['tools'] === ['查找可用工具'] && $searchExecutions === 1, '无效参数调用被记作已执行');
$service->acknowledge($agent, $reply);

$repeated = [];
for ($i = 0; $i < 6; $i++) {
    $call = $searchCall;
    $call['tool_calls'][0]['id'] = 'search-' . $i;
    $repeated[] = $call;
}
$http = new FixtureHttp($repeated);
$context = new AgentContext($auth, 1, 0, 200011);
$agent = new SearchFixtureAgent($context, new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http), $definitions);
try {
    $service->ask($agent, '故意重复查找');
    throw new RuntimeException('重复工具查找未停止');
} catch (DomainException $e) { check(str_contains($e->getMessage(), '已停止'), '超限错误仍被隐藏'); }
check(count($http->offeredTools) === 4 && $searchExecutions === 1, '达到检索上限后仍继续请求模型或执行业务');

// 停止标识绑定身份，旧请求的停止不能影响新请求或其他管理员。
$control = new \app\common\agent\AgentRunControl(0, 1, bin2hex(random_bytes(16)));
$control->stop();
(new \app\common\agent\AgentRunControl(0, 2, $control->requestId))->check(true);
(new \app\common\agent\AgentRunControl(0, 1, bin2hex(random_bytes(16))))->check(true);
$context = new AgentContext($auth, 1, 0, 200020, control: $control);
$http = new FixtureHttp([]);
$agent = new SearchFixtureAgent($context, new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http), $definitions);
$reply = $service->ask($agent, '提前停止');
check($reply['stopped'] && $http->offeredTools === [] && $agent->inspect() === null, '模型请求开始前停止无效或运行状态未清理');

$control = new \app\common\agent\AgentRunControl(0, 1, bin2hex(random_bytes(16)));
$context = new AgentContext($auth, 1, 0, 200021, control: $control);
$http = new FixtureHttp([['role' => 'assistant', 'content' => '已生成的部分回答']]);
$agent = new SearchFixtureAgent($context, new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http), $definitions);
$reply = $service->ask($agent, '输出时停止', function ($event) use ($control) { if ($event === 'delta') $control->stop(); });
check($reply['stopped'] && str_contains($reply['answer'], '已生成的部分回答') && $agent->inspect() === null, '停止后部分回答丢失或会话仍被占用');

$control = new \app\common\agent\AgentRunControl(0, 1, bin2hex(random_bytes(16)));
$context = new AgentContext($auth, 1, 0, 200022, control: $control);
$batch = $articleCall;
$batch['tool_calls'][] = ['id' => 'article-2', 'type' => 'function', 'function' => ['name' => 'cms_article_read', 'arguments' => '{"id":2}']];
$http = new FixtureHttp([$searchCall, $batch]);
$before = $searchExecutions;
$agent = new SearchFixtureAgent($context, new AiModelProvider('https://fixture.invalid/v1', 'fixture', 'fixture', httpClient: $http), $definitions);
$reply = $service->ask($agent, '工具间停止', function ($event, $data) use ($control) {
    if ($event === 'tool_done' && $data['name'] === '查询文章') $control->stop();
});
check($reply['stopped'] && $searchExecutions === $before + 1 && count($http->offeredTools) === 2, '停止后仍执行后续工具或模型请求');
check(in_array('查询文章', $reply['tools'], true) && $agent->inspect() === null, '已完成工具的记录丢失或停止未释放会话');
$context = new AgentContext($auth, 1, 0, 200022);
$agent = new SearchFixtureAgent($context, model([['role' => 'assistant', 'content' => '可以继续对话']]), $definitions);
$reply = $service->ask($agent, '继续');
check($reply['answer'] === '可以继续对话', '停止后无法继续当前会话');
$service->acknowledge($agent, $reply);

echo "数组配置与分组注册、旧 Provider 兼容、插件功能说明、参数错误/权限隔离、手动译文审批、整项任务一次审批及自动批准、数据库恢复、批准/拒绝、重复提交和执行前权限检查、中文工具发现和重复检索停止、主动停止及停止后继续对话通过；真实模型请求 0 次\n";

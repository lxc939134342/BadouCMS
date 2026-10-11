<?php

// 在临时项目与随机数据库验证原生禁用、收回文件及现有两套打包入口。
$project = dirname(__DIR__, 2);
$loader = require $project . '/vendor/autoload.php';
require_once $project . '/vendor/topthink/framework/src/helper.php';
$originalApp = new think\App($project . '/');
$originalApp->env->load($project . '/.env');
$databaseConfig = require $project . '/config/database.php';
$original = $databaseConfig['connections'][$databaseConfig['default']];
$temporary = sys_get_temp_dir() . '/badou_agent_plugin_' . bin2hex(random_bytes(6));
mkdir($temporary, 0755, true);
$temporary = realpath($temporary);
badou\Filesystem::copydirs($project . '/config', $temporary . '/config');
badou\Filesystem::copydirs($project . '/modules/aiagent', $temporary . '/modules/aiagent');
$module = $temporary . '/modules/aiagent';
$owned = json_decode(file_get_contents($module . '/.modulerc'), true)['files'];
foreach (array_merge($owned, ['app/common.php', 'app/service.php', 'app/admin/view/index/index.html']) as $file) {
    $destination = $temporary . '/' . $file;
    if (!is_dir(dirname($destination))) mkdir(dirname($destination), 0755, true);
    copy($project . '/' . $file, $destination);
}
mkdir($temporary . '/app/view/common', 0755, true);
file_put_contents($temporary . '/app/view/common/modules.html', '');
file_put_contents($module . '/info.ini', str_replace('state = 1', 'state = 0', file_get_contents($module . '/info.ini')));
define('MODULE_PATH', $temporary . '/modules/');
$loader->addPsr4('modules\\aiagent\\', $module . '/', true);
$app = new think\App($temporary . '/');
$app->initialize();
// 临时项目不复制 .env，数据库连接仅在本测试进程中使用。
think\facade\Config::set(['default' => 'fixture', 'stores' => ['fixture' => [
    'type' => 'File', 'path' => $temporary . '/cache/']]], 'cache');
think\facade\Cache::forgetDriver();
set_exception_handler(static function (Throwable $e): void { fwrite(STDERR, $e->getMessage() . PHP_EOL); exit(1); });

use app\admin\model\{AdminRule, AgentChat};
use modules\aiagent\{Aiagent, library\Runtime};
use think\facade\{Db, Config, Event};
use badou\Server;

function check(bool $value, string $message): void
{
    if (!$value) throw new RuntimeException($message);
}
function cleanup(string $directory): void
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory,
        FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $file) $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    rmdir($directory);
}

$database = 'badou_agent_plugin_' . bin2hex(random_bytes(6));
$root = Db::connect($original);
$created = false;
try {
    check(root_path() === $temporary . '/', '禁止操作真实项目');
    $agentConfig = require $temporary . '/config/agent.php';
    check(Config::get('agent') === $agentConfig, '框架未自动加载根配置');
    check(!is_file($module . '/config/agent.php'), '启用状态仍保留重复配置');
    $root->execute('CREATE DATABASE `' . $database . '` CHARACTER SET utf8mb4');
    $created = true;
    $fixture = array_replace($original, ['database' => $database, 'prefix' => 'test_', 'trigger_sql' => false]);
    Config::set(['default' => 'fixture', 'connections' => ['fixture' => $fixture, 'mysql' => $fixture]], 'database');
    Db::setCache(think\facade\Cache::store());
    preg_match('/CREATE TABLE `bd_admin_rule`.*?;/s', file_get_contents($project . '/extend/badou/install/badouadmin-install.sql'), $table);
    Db::execute(str_replace('`bd_', '`test_', $table[0]));
    $plugin = new Aiagent();
    check($plugin->install(), '插件安装失败');
    check(Config::get('agent') === $agentConfig, '插件启动改变根配置');
    $ids = AdminRule::column('id', 'name');
    check(count($ids) === 10 && count(Db::query('SHOW TABLES')) === 6, '安装缺少菜单或数据表');
    $chat = AgentChat::createForUser(0, 1, '原生插件测试');
    $chat->appendExchange('原消息', '原回答', []);
    Db::name('agent_patch')->insert(['chat_id' => $chat->id, 'status' => 'pending', 'file' => 'fixture.php']);
    check($plugin->install() && AdminRule::column('id', 'name') === $ids, '重复安装改变已有权限 ID');
    Server::setModuleInfo('aiagent', array_replace(Server::getModuleInfo('aiagent'), ['state' => 1]));
    $plugin->AppInit();
    check(Runtime::enabled(), '启用状态未识别');
    check(str_starts_with((new ReflectionClass(NeuronAI\Agent\Agent::class))->getFileName(), $module . '/vendor/'), '没有使用插件 SDK');
    check(!is_dir($project . '/vendor/neuron-core/neuron-ai'), 'SDK 仍留在根 vendor');
    $catalog = (new modules\packageframe\library\Package())->getModuleCatalog();
    check(isset($catalog['aiagent']), '现有插件列表不识别 aiagent');
    $packer = new modules\packageframe\library\Package();
    $excluded = (new ReflectionMethod($packer, 'getExcludedGlobalPaths'))->invoke($packer, $catalog, []);
    check(count(array_intersect($owned, array_keys($excluded))) === count($owned), '未选插件的框架包仍会夹带 Agent 全局文件');
    check($app->console->getCommand('agent:task')->getName() === 'agent:task', '插件没有注册 worker');
    $auth = new class { public function check($rule): bool { return true; } };
    check(str_contains(implode('', Event::trigger('admin_body_end', ['auth' => $auth])), 'id="badou-agent-launcher"'), '启用后没有浮窗');

    // 用户修改的根配置随禁用原样收回，打包后仍可恢复。
    $agentConfig['stream_tools'] = false;
    $agentConfig['queue']['mode'] = 'cli';
    $agentConfig['patch']['allowed_ext'] = ['php'];
    $agentConfig['patch']['deny_files'] = [];
    file_put_contents($temporary . '/config/agent.php', '<?php return ' . var_export($agentConfig, true) . ';');
    Config::load($temporary . '/config/agent.php', 'agent');
    Runtime::boot();
    check(Config::get('agent') === $agentConfig, '修改后的根配置未生效');
    $configContents = file_get_contents($temporary . '/config/agent.php');

    // 直接运行框架的真实禁用方法，所有 root_path/MODULE_PATH 均已限定到临时项目。
    check(Server::disable('aiagent'), '原生禁用失败');
    foreach ($owned as $file) {
        check(!is_file($temporary . '/' . $file) && is_file($module . '/' . $file), '框架没有收回文件：' . $file);
        $expected = $file === 'config/agent.php' ? $configContents : file_get_contents($project . '/' . $file);
        check(file_get_contents($module . '/' . $file) === $expected, '收回的功能代码不一致：' . $file);
    }
    check(in_array('config/agent.php', Server::getGlobalFiles('aiagent'), true), '启用迁移目录未识别根配置');
    file_put_contents($temporary . '/config/agent.php', '<?php return [];');
    check(in_array('config/agent.php', Server::getGlobalFiles('aiagent', true), true), '根配置冲突未被检测');
    unlink($temporary . '/config/agent.php');
    check(!Runtime::enabled() && AdminRule::where('status', 'hidden')->count() === 10, '原生禁用没有同步状态和菜单');
    check(implode('', Event::trigger('admin_body_end', ['auth' => $auth])) === '', '禁用后浮窗仍出现');
    $worker = $app->console->getCommand('agent:task');
    check((new ReflectionMethod($worker, 'execute'))->invoke($worker, new think\console\Input(), new think\console\Output('buffer')) === 1,
        '禁用后 worker 仍执行任务');
    check(Db::name('agent_chat')->count() === 1 && Db::name('agent_patch')->count() === 1, '禁用丢失会话或审批');

    $archives = [(new modules\moduledev\library\Module())->package('aiagent'),
        (new modules\packageframe\library\Package())->buildPluginPackage('aiagent')['file']];
    foreach ($archives as $archive) {
        check(is_string($archive) && is_file($archive), '现有打包入口未生成插件包');
        $zip = new ZipArchive();
        check($zip->open($archive) === true, '打包产物无法打开');
        foreach (array_merge($owned, ['info.ini', '.modulerc', 'Aiagent.php', 'install.sql', 'config/agent.php', 'vendor/autoload.php',
            'vendor/neuron-core/neuron-ai/src/Agent/Agent.php']) as $entry) {
            check($zip->locateName($entry) !== false, '现有打包程序遗漏：' . $entry);
        }
        check($zip->locateName('app/admin/view/index/index.html') === false, '插件错误包含整个后台主模板');
        check($zip->getFromName('config/agent.php') === $configContents, '打包丢失修改后的根配置');
        check($zip->locateName('settings/agent.php') === false, '打包仍包含旧配置路径');
        check($zip->locateName('.env') === false && $zip->locateName('build.py') === false, '含私有配置或额外构建器');
        $zip->close();
    }
    check($plugin->uninstall() && AdminRule::count() === 0, '卸载没有移除菜单');
    check(Db::name('agent_chat')->count() === 1 && Db::name('agent_patch')->value('status') === 'pending', '卸载丢失历史或审批');
    Config::set(['agent' => []]);
    check($plugin->install() && AgentChat::findForUser((int)$chat->id, 0, 1)->title === '原生插件测试', '重装丢失历史');
    check(Config::get('agent') === $agentConfig, '安装阶段未加载插件包配置');
    echo 'PASS 根配置自动加载、修改保留与冲突检测、安装包配置加载、原生插件列表、独立 Composer SDK、权限 ID、真实 Server 禁用及 71 个文件收回、两条原生打包入口、禁用／卸载保留历史与审批' . PHP_EOL;
} finally {
    if ($created) $root->execute('DROP DATABASE `' . $database . '`');
    cleanup($temporary);
}

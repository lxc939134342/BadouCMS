<?php

// 所有安装、解压与文件迁移测试都限定在临时项目，不使用数据库或官网接口。
$project = dirname(__DIR__, 2);
$loader = require $project . '/vendor/autoload.php';
require_once $project . '/vendor/topthink/framework/src/helper.php';
if (in_array('--upgrade-payload', $argv, true)) {
    $loader->addClassMap(['badou\\Server' => $project . '/modules/upgrade/up/extend/badou/Server.php']);
}
$temporary = sys_get_temp_dir() . '/badou_config_guard_' . bin2hex(random_bytes(6));
mkdir($temporary . '/config', 0755, true);
mkdir($temporary . '/modules', 0755, true);
foreach (['app.php', 'badouadmin.php'] as $filename) {
    file_put_contents($temporary . '/config/' . $filename, '<?php return ["fixture" => "保留原始配置"];');
}
file_put_contents($temporary . '/config/cache.php', '<?php return ' . var_export([
    'default' => 'file', 'stores' => ['file' => ['type' => 'File', 'path' => $temporary . '/cache/']],
], true) . ';');
define('MODULE_PATH', $temporary . '/modules/');
$app = new think\App($temporary . '/');
$app->initialize();
think\facade\Config::set(['default' => 'fixture', 'stores' => ['fixture' => [
    'type' => 'File', 'path' => $temporary . '/cache/']]], 'cache');
think\facade\Cache::forgetDriver();
set_exception_handler(static function (Throwable $e): void { fwrite(STDERR, $e->getMessage() . PHP_EOL); exit(1); });

use badou\Server;

function verify(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

function reject(callable $operation): void
{
    try {
        $operation();
    } catch (think\Exception $e) {
        verify(str_contains($e->getMessage(), '插件禁止写入或替换框架配置：'), '操作失败原因不是核心配置保护');
        return;
    }
    throw new RuntimeException('核心配置未被阻止');
}

function removeFixture(string $directory): void
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory,
        FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $file) $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    rmdir($directory);
}

function fixtureArchive(string $file, string $path): void
{
    $zip = new ZipArchive();
    verify($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, '无法创建安装包');
    $zip->addFromString('config/agent.php', '<?php return ["stream_tools" => true];');
    $zip->addFromString($path, '<?php return [];');
    $zip->close();
}

try {
    verify(root_path() === $temporary . '/', '禁止操作真实项目');
    $originals = [];
    foreach (['config/app.php', 'config/badouadmin.php'] as $path) $originals[$path] = file_get_contents($temporary . '/' . $path);

    foreach (['config/app.php', 'config/badouadmin.php', 'CONFIG/APP.PHP', 'config\\badouadmin.php', 'config/./app.php'] as $path) {
        foreach (['unzip', 'upgrade', 'install', 'force-install'] as $operation) {
            $archive = $temporary . '/fixture.zip';
            fixtureArchive($archive, $path);
            reject(static function () use ($operation, $archive): void {
                if ($operation === 'unzip') Server::unzip('fixture', $archive);
                elseif ($operation === 'upgrade') Server::upgradeUnzip('fixture', $archive, false);
                else Server::install('fixture', $operation === 'force-install', [], $archive);
            });
            verify(!is_file(MODULE_PATH . 'fixture/config/agent.php'), '拒绝前已写入安装包文件');
            if (is_dir(MODULE_PATH . 'fixture')) removeFixture(MODULE_PATH . 'fixture');
        }
    }

    // 内容相同也必须拒绝，不能因未被判定为冲突而跳过保护。
    mkdir(MODULE_PATH . 'fixture/config', 0755, true);
    foreach (['config/app.php', 'config/badouadmin.php'] as $path) {
        file_put_contents(MODULE_PATH . 'fixture/' . $path, $originals[$path]);
        reject(static fn () => Server::getGlobalFiles('fixture'));
        reject(static fn () => Server::getGlobalFiles('fixture', true));
        reject(static fn () => Server::check('fixture'));
        foreach ([false, true] as $force) {
            reject(static fn () => Server::enable('fixture', $force));
            reject(static fn () => Server::disable('fixture', $force));
            reject(static fn () => Server::uninstall('fixture', $force));
        }
        unlink(MODULE_PATH . 'fixture/' . $path);
    }

    // 磁盘没有待迁移文件时，禁用会使用归属清单，同样不能删除或收回核心配置。
    foreach (['./config/app.php', 'config\\badouadmin.php', 'CONFIG/APP.PHP', 'config/sub/../app.php'] as $path) {
        file_put_contents(MODULE_PATH . 'fixture/.modulerc', json_encode(['files' => [$path]]));
        reject(static fn () => Server::disable('fixture', true));
        reject(static fn () => Server::enable('fixture', true));
    }
    unlink(MODULE_PATH . 'fixture/.modulerc');
    file_put_contents(MODULE_PATH . 'fixture/config/agent.php', '<?php return [];');
    file_put_contents(MODULE_PATH . 'fixture/config.php', '<?php return [];');
    verify(Server::getGlobalFiles('fixture') === ['config/agent.php'], '插件自有配置无法迁移');
    $allowedArchive = $temporary . '/allowed.zip';
    fixtureArchive($allowedArchive, 'config.php');
    Server::unzip('allowed', $allowedArchive);
    verify(Server::getGlobalFiles('allowed') === ['config/agent.php'], '普通配置安装包无法解压');
    Server::upgradeUnzip('allowed', $allowedArchive, false);
    verify(is_file(MODULE_PATH . 'allowed/config/agent.php'), '普通配置升级包无法解压');
    foreach ($originals as $path => $content) verify(file_get_contents($temporary . '/' . $path) === $content, '核心配置被更改或删除：' . $path);
    echo 'PASS 安装/强制安装、升级解压、启用/强制启用、禁用/卸载、同内容文件与归属清单保护，普通插件配置正常识别' . PHP_EOL;
} finally {
    removeFixture($temporary);
}

<?php

namespace modules\aiagent\library;

use Composer\InstalledVersions;
use think\facade\Config;

class Runtime
{
    public static function enabled(): bool
    {
        // 常驻 worker 每次重新读文件，避免禁用后继续消费后续任务。
        $info = @parse_ini_file(dirname(__DIR__) . '/info.ini');
        return is_array($info) && (int)($info['state'] ?? 0) === 1;
    }

    public static function boot(): void
    {
        self::checkHost();
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        // 启用时使用根配置；安装阶段文件尚未迁移，读取安装包中的同一份配置。
        $configFile = root_path() . 'config/agent.php';
        if (!is_file($configFile)) $configFile = dirname(__DIR__) . '/config/agent.php';
        $defaults = require $configFile;
        $existing = (array)Config::get('agent', []);
        $merged = array_replace_recursive($defaults, $existing);
        // 列表型策略整体覆盖，避免较短的站点配置意外带上默认列表的剩余项。
        foreach (['allowed_ext', 'deny_prefix', 'deny_files'] as $key) {
            if (array_key_exists($key, $existing['patch'] ?? [])) {
                $merged['patch'][$key] = $existing['patch'][$key];
            }
        }
        Config::set($merged, 'agent');
    }

    private static function checkHost(): void
    {
        if (PHP_VERSION_ID < 80100 || PHP_VERSION_ID >= 80500) {
            throw new \RuntimeException('AI 后台助手当前支持 PHP 8.1—8.4');
        }
        foreach (['curl', 'json', 'pdo', 'mbstring', 'zip', 'xmlreader'] as $extension) {
            if (!extension_loaded($extension)) {
                throw new \RuntimeException('AI 后台助手缺少 PHP 扩展：' . $extension);
            }
        }
        foreach (['GuzzleHttp\\Client', 'PhpOffice\\PhpSpreadsheet\\Reader\\Xlsx',
            'app\\common\\service\\TableDesignService', 'app\\admin\\command\\Crud'] as $class) {
            if (!class_exists($class)) {
                throw new \RuntimeException('AI 后台助手缺少框架组件，请先更新 BadouCMS：' . $class);
            }
        }
        $shell = root_path() . 'app/admin/view/index/index.html';
        if (!is_file($shell) || !str_contains(file_get_contents($shell), "hook('admin_body_end'")) {
            throw new \RuntimeException('后台模板缺少 admin_body_end 钩子，请按插件 README 更新后台入口');
        }
        if (InstalledVersions::isInstalled('neuron-core/neuron-ai')
            && ltrim((string)InstalledVersions::getPrettyVersion('neuron-core/neuron-ai'), 'v') !== '4.0.0') {
            throw new \RuntimeException('宿主 Neuron AI 版本与插件不兼容，需要 4.0.0');
        }
    }
}

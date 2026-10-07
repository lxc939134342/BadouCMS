<?php

namespace app\common\agent\tools;

use app\common\agent\AgentContext;
use app\common\agent\AbstractAgentTool;
use think\facade\Db;

class SystemInfoTool extends AbstractAgentTool
{
    protected string $label = '查看系统版本';

    public function name(): string { return 'system_info'; }

    public function description(): string { return '查询当前 badoucms 后台的运行环境信息，包括 badoucms、PHP、MySQL/MariaDB、Web 服务器（Nginx/Apache）、操作系统及常用 PHP 配置限制。'; }

    public function permission(): string { return 'agent/index'; }

    public function properties(): array { return []; }

    public function execute(AgentContext $context, array $input): array
    {
        return [
            'badoucms' => [
                'version' => (string)config('badouadmin.version'),
            ],
            'php' => [
                'version' => PHP_VERSION,
                'sapi' => PHP_SAPI,
            ],
            'database' => $this->databaseInfo(),
            'web_server' => $this->webServer(),
            'server' => [
                'os' => php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('m'),
                'hostname' => php_uname('n'),
                'server_name' => (string)($_SERVER['SERVER_NAME'] ?? ''),
                'server_addr' => (string)($_SERVER['LOCAL_ADDR'] ?? ($_SERVER['SERVER_ADDR'] ?? '')),
                'server_port' => (string)($_SERVER['SERVER_PORT'] ?? ''),
            ],
            'limits' => [
                'upload_max_filesize' => (string)ini_get('upload_max_filesize'),
                'post_max_size' => (string)ini_get('post_max_size'),
                'max_execution_time' => (string)ini_get('max_execution_time'),
                'memory_limit' => (string)ini_get('memory_limit'),
            ],
        ];
    }

    private function databaseInfo(): array
    {
        $type = strtolower((string)config('database.default'));
        $version = '';
        try {
            $type = strtolower((string)Db::connect()->getConfig('type'));
            if (str_contains($type, 'mysql')) {
                $row = Db::query('SELECT VERSION() AS version');
                $version = (string)($row[0]['version'] ?? '');
            }
        } catch (\Throwable) {
        }
        return ['type' => $type, 'version' => $version];
    }

    private function webServer(): array
    {
        $software = trim((string)($_SERVER['SERVER_SOFTWARE'] ?? ''));
        $name = '';
        $version = '';
        if ($software !== '') {
            if (preg_match('#nginx/?([0-9][0-9.]*)?#i', $software, $matches)) {
                $name = 'nginx';
                $version = $matches[1] ?? '';
            } elseif (preg_match('#apache/?([0-9][0-9.]*)?#i', $software, $matches)) {
                $name = 'Apache';
                $version = $matches[1] ?? '';
            }
        }
        if ($name === '') {
            foreach (['nginx' => 'nginx -v', 'Apache' => 'apachectl -v'] as $candidate => $command) {
                $output = $this->commandVersion($command);
                if ($output !== '') {
                    $name = $candidate;
                    $version = $output;
                    break;
                }
            }
        }
        return [
            'software' => $software !== '' ? $software : '未知',
            'name' => $name !== '' ? $name : '未知',
            'version' => $version,
        ];
    }

    private function commandVersion(string $command): string
    {
        if (!function_exists('shell_exec')) {
            return '';
        }
        $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
        if (in_array('shell_exec', $disabled, true)) {
            return '';
        }
        $output = @shell_exec($command . ' 2>&1');
        if (!is_string($output) || trim($output) === '') {
            return '';
        }
        return preg_match('/([0-9]+\.[0-9]+(?:\.[0-9]+)?)/', $output, $matches) ? $matches[1] : '';
    }
}

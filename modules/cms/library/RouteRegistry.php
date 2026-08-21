<?php

namespace modules\cms\library;

use RuntimeException;

/** 基于文件存储的前台扩展路由配置。 */
class RouteRegistry
{
    public static function path(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'routes.php';
    }

    public static function all(): array
    {
        $path = self::path();
        if (!is_file($path)) {
            return [];
        }

        $routes = include $path;
        if (!is_array($routes)) {
            return [];
        }

        return array_filter($routes, static function ($target, $rule) {
            return is_string($rule) && is_string($target) && trim($rule) !== '' && trim($target) !== '';
        }, ARRAY_FILTER_USE_BOTH);
    }

    public static function save(array $routes): void
    {
        $normalized = [];
        foreach ($routes as $rule => $target) {
            $rule = trim((string)$rule);
            $target = trim((string)$target);
            if ($rule === '' || $target === '') {
                continue;
            }
            if (str_contains($rule, "\0") || str_contains($target, "\0")) {
                throw new RuntimeException('路由规则不能包含空字符');
            }
            if (isset($normalized[$rule])) {
                throw new RuntimeException('路由规则不能重复：' . $rule);
            }
            $normalized[$rule] = $target;
        }

        $path = self::path();
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('无法创建 CMS 路由目录');
        }

        $temporary = $path . '.tmp.' . getmypid();
        $content = "<?php\n\nreturn " . var_export($normalized, true) . ";\n";
        if (file_put_contents($temporary, $content, LOCK_EX) === false) {
            throw new RuntimeException('无法写入 CMS 路由文件');
        }
        if (!rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('无法更新 CMS 路由文件');
        }
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($path, true);
        }
    }
}

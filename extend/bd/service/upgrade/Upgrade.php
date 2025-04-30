<?php

// +----------------------------------------------------------------------
// | BADOUCMS [ 八斗网站系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2024-2030 http://doc.ldcode.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: lande <939134342@qq.com>
// +----------------------------------------------------------------------

namespace bd\service\upgrade;

use Throwable;
use ba\Exception;
use PhpZip\ZipFile;
use think\facade\Db;
use GuzzleHttp\Client;
use think\facade\Config;
use GuzzleHttp\Exception\TransferException;
use Symfony\Component\VarExporter\VarExporter;

class Upgrade
{
    protected static function backupPath()
    {
        return root_path().'backup'.DIRECTORY_SEPARATOR.'upgrade'. DIRECTORY_SEPARATOR ;
    }

    protected static function getUpgradePath()
    {
        return root_path().'extend'.DIRECTORY_SEPARATOR.'bd'.DIRECTORY_SEPARATOR.'service'.DIRECTORY_SEPARATOR.'upgrade'.DIRECTORY_SEPARATOR;
    }

    protected static $ignorePackageDirs = [
        '.git', '.svn', '.settings', '.vscode', 'node_modules',
        'dist_electron','backup','storage',
        'runtime','.idea'
    ];

    //忽略的包文件
    protected static $ignorePackageFiles = ['.DS_Store', 'Thumbs.db', 'readme.md', 'phpunit.xml','.phpunit.result.cache'];

    /**
     * 验证程序是否有最新版本
     * @param $name
     * @return array|string
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function version($name)
    {
        try {
            $client   = self::getClient();
            $response = $client->get('/api/program/' . 'getLast', ['query' => ['name' => $name]]);
            $body     = $response->getBody();
            $content  = $body->getContents();
            if ($content == '' || stripos($content, '<title>系统发生错误</title>') !== false) {
                throw new Exception('package download failed', 0);
            }
            if (str_starts_with($content, '{')) {
                $content = (array)json_decode($content, true);
            }
        } catch (TransferException $e) {
            throw new Exception('package download failed', 0, ['msg' => $e->getMessage()]);
        }
        if ($content['code'] == 1) {
            return $content['data'];
        }
        return false;
    }

    /**
     * 获取当前版本之后的所有版本
     * @param $name
     * @param $version
     * @return false|mixed
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getList($name, $version)
    {
        try {
            $client   = self::getClient();
            $response = $client->get('/api/program/' . 'getList', ['query' => ['name' => $name,'version' => $version]]);
            $body     = $response->getBody();
            $content  = $body->getContents();
            if ($content == '' || stripos($content, '<title>系统发生错误</title>') !== false) {
                throw new Exception('package download failed', 0);
            }
            if (str_starts_with($content, '{')) {
                $content = (array)json_decode($content, true);
            }
        } catch (TransferException $e) {
            throw new Exception('package download failed', 0, ['msg' => $e->getMessage()]);
        }
        if ($content['code'] == 1) {
            return $content['data'];
        }
        return false;
    }

    public static function download($name, $version)
    {
        $downloadDir = runtime_path() .'badoucms'.DIRECTORY_SEPARATOR.'upgrade'. DIRECTORY_SEPARATOR;
        if (!is_dir($downloadDir)) {
            @mkdir($downloadDir, 0755, true);
        }
        $tmpFile =  $downloadDir. $name .'-'. $version .'.zip';
        try {
            $client   = self::getClient();
            $response = $client->get('/api/program/' . 'download', ['query' => ['name' => $name,'version' => $version]]);
            $body     = $response->getBody();
            $content  = $body->getContents();
            if ($content == '' || stripos($content, '<title>系统发生错误</title>') !== false) {
                throw new Exception('package download failed', 0);
            }
            if (str_starts_with($content, '{')) {
                $json = (array)json_decode($content, true);
                //如果传回的是一个下载链接,则再次下载
                if ($json['data'] && isset($json['data']['url'])) {
                    $response = $client->get($json['data']['url']);
                    $body = $response->getBody();
                    $content = $body->getContents();
                } else {
                    //下载返回错误，抛出异常
                    throw new Exception($json['msg'], $json['code'], $json['data'] ?? []);
                }
            }
        } catch (TransferException $e) {
            throw new Exception('package download failed', 0, ['msg' => $e->getMessage()]);
        }
        if ($write = fopen($tmpFile, 'w')) {
            fwrite($write, $content);
            fclose($write);
            return $tmpFile;
        }

        throw new Exception("No permission to write temporary files");
    }

    // 备份程序
    public static function backup($name, $version)
    {
        $backupDir = self::backupPath() ;
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0755, true);
        }
        $backupFile = $backupDir . $name . '-' . $version.'-'.date('YmdHis') . '.zip';
        if (!class_exists('ZipArchive')) {
            throw new Exception('ZipArchive 未安装');
        }

        $zip = new \ZipArchive();
        $zip->open($backupFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(root_path()), \RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativeFilePath = str_replace(root_path(), '', $filePath);
                if (!self::ignorePackageToken($relativeFilePath, [])) {
                    if ($filePath == '' || $relativeFilePath == '') {
                        continue;
                    }
                    $zip->addFile($filePath, $relativeFilePath);
                }
            }
        }
        $zip->close();
    }

    /**
     * 获取请求对象
     * @return Client
     */
    protected static function getClient()
    {
        $options = [
            'base_uri'        => Config::get('badoucms.api_url'),
            'timeout'         => 60,
            'connect_timeout' => 60,
            'verify'          => false,
            'http_errors'     => false,
            'headers'         => [
                'X-REQUESTED-WITH' => 'XMLHttpRequest',
                'Referer'          => dirname(request()->root(true)),
                'User-Agent'       => 'BADOUCMSClient',
            ]
        ];
        return new Client($options);
    }

    protected static function ignorePackageToken($relativePath, $extend_tokends, $ignoreDirs = [], $ignoreFiles = [])
    {
        // 如果没有传入忽略目录，则使用默认值
        $ignoreDirs = $ignoreDirs ? $ignoreDirs : self::$ignorePackageDirs;

        // 如果没有传入忽略文件，则使用默认值
        $ignoreFiles = $ignoreFiles ? $ignoreFiles : self::$ignorePackageFiles;

        // 检查目录
        foreach ($ignoreDirs as $token) {
            if (static::ignoreMatch($token, $relativePath)) {
                return true;
            }
        }

        // 检查文件
        $filename = basename($relativePath);
        if (in_array($filename, $ignoreFiles)) {
            return true;
        }

        // 检查额外的忽略规则
        if (is_array($extend_tokends)) {
            foreach ($extend_tokends as $token) {
                if (static::ignoreMatch($token, $relativePath)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function ignoreMatch($pattern, $string)
    {
        $pattern = str_replace('\\', '/', $pattern);
        $firstChar = substr($pattern, 0, 1);
        if ($firstChar == '!') {
            $pattern = '#' . substr($pattern, 1)  . '#us';
            return preg_match($pattern, $string) !== 1;
        } elseif ($firstChar === '/') {
            $pattern = '#^' . substr($pattern, 1)  . '#us';
        } else {
            $pattern = '#' . $pattern  . '#us';
        }
        return preg_match($pattern, $string) === 1;
    }

    /**
     * 解压Zip
     * @param string $file ZIP文件路径
     * @param string $dir 解压路径
     * @param array $ignoreDirs 要忽略的目录列表
     * @param array $ignoreFiles 要忽略的文件列表
     * @return string 解压后的路径
     * @throws Throwable
     */
    public static function unzip($file, $dir = '', $ignoreDirs = [], $ignoreFiles = [])
    {
        if (!file_exists($file)) {
            throw new Exception("Zip文件不存在");
        }

        $zip = new ZipFile();
        try {
            $zip->openFile($file);

            // 修改这里的过滤逻辑，使用 ignorePackageToken
            $entries = array_filter($zip->getListFiles(), function ($entryName) use ($ignoreDirs, $ignoreFiles) {
                // 如果文件需要被忽略，返回 false
                if (self::ignorePackageToken($entryName, '', $ignoreDirs, $ignoreFiles)) {
                    return false;
                }
                return true;
            });

            $dir = $dir ?: substr($file, 0, strripos($file, '.zip'));
            if (!is_dir($dir)) {
                @mkdir($dir, 0755);
            }

            // 只解压不需要忽略的文件
            $zip->extractTo($dir, $entries);

        } catch (Throwable $e) {
            throw new Exception('无法解压ZIP文件', 0, ['msg' => $e->getMessage()]);
        } finally {
            $zip->close();
        }

        return $dir;
    }

    /**
     * 执行sql
     * @param string $fileName 文件名
     * @return bool
     */
    public static function sql($fileName = 'update.sql')
    {
        $fileName = is_null($fileName) ? 'update.sql' : $fileName;
        $sqlFile = self::getUpgradePath() . $fileName;
        if (is_file($sqlFile)) {
            $lines = file($sqlFile);
            $templine = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*') {
                    continue;
                }

                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $templine = str_ireplace('__PREFIX__', config('database.prefix'), $templine);
                    $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                    try {
                        Db::execute($templine);
                    } catch (\PDOException $e) {
                        //$e->getMessage();
                    }
                    $templine = '';
                }
            }
        }
        return true;
    }

    /**
     * 升级后钩子
     * @param string $name 程序名
     * @param string $version 版本号
     * @return void
     */
    public static function upgradeAfter($name, $version)
    {
        trace('error', '升级后钩子');
        self::siteConfig(['version' => $version]);
    }

    public static function siteConfig($conf)
    {
        $configFile = root_path() . 'extend' . DIRECTORY_SEPARATOR . 'bd' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'badoucms.php';

        // 获取当前配置
        $config = include $configFile;

        // 修改版本号
        $config = array_merge($config, $conf);

        // 生成新的配置文件内容
        $content = "<?php\n\nreturn " . VarExporter::export($config) . ";\n";

        // 写入文件
        $ret = file_put_contents($configFile, $content, LOCK_EX);
        if ($ret === false) {
            throw new Exception('无法写入配置文件');
        }
    }
}

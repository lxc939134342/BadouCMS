<?php

namespace badou;

use PhpZip\ZipFile;
use think\Exception;
use think\facade\Db;
use GuzzleHttp\Client;
use think\facade\Config;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use PhpZip\Exception\ZipException;
use GuzzleHttp\Exception\TransferException;

/**
 * 模块服务
 */
class Server
{
    /**
     * 模块列表
     */
    public static function modules($params = [])
    {
        $params['domain'] = request()->host(true);
        return self::sendRequest('module/index', $params, 'GET');
    }

    public static function getDbConfig()
    {
        $dbConfig         = Config::get('database');
        $dbconnect_config = $dbConfig['connections'][$dbConfig['default']];
        return $dbconnect_config;
    }
    /**
     * 获得已安装的模块列表
     * @return array
     */
    public static function getInstalldModuleList()
    {
        $results = scandir(MODULE_PATH);
        $list = [];
        foreach ($results as $name) {
            if ($name === '.' or $name === '..') {
                continue;
            }
            if (is_file(MODULE_PATH . $name)) {
                continue;
            }
            $moduleDir = MODULE_PATH . $name . DS;
            if (!is_dir($moduleDir)) {
                continue;
            }

            if (!is_file($moduleDir . ucfirst($name) . '.php')) {
                continue;
            }

            $info_file = $moduleDir . 'info.ini';
            if (!is_file($info_file)) {
                continue;
            }

            $info = Config::load($info_file, $name);
            if (!isset($info['name'])) {
                continue;
            }
            $is_testdata = is_file(self::getTestdataFile($name));
            $info['is_testdata'] = $is_testdata;
            $list[$name] = $info;
        }
        return $list;
    }

    /**
     * 检测模块是否购买授权
     */
    public static function isBuy($name, $extend = [])
    {
        $params = array_merge(['name' => $name, 'domain' => request()->host(true)], $extend);
        return self::sendRequest('module/isbuy', $params, 'POST');
    }

    /**
     * 检测模块是否授权
     *
     * @param string $name   模块名称
     * @param string $domain 验证域名
     */
    public static function isAuthorization($name, $domain = '')
    {
        $config = self::config($name);
        $request = request();
        $domain = self::getRootDomain($domain ? $domain : $request->host(true));
        if (isset($config['domains']) && isset($config['validations']) && isset($config['licensecodes'])) {
            $index = array_search($domain, $config['domains']);
            if ((in_array($domain, $config['domains']) && in_array(md5(md5($domain) . ($config['licensecodes'][$index] ?? '')), $config['validations'])) || $request->isCli()) {
                return true;
            }
        }
        return false;
    }

    /**
     * 插件详情
     * @param mixed $name
     * @param mixed $extend
     * @return array
     */
    public static function moduleInfo($name, $extend = [])
    {
        $params = array_merge(['name' => $name, 'domain' => request()->host(true)], $extend);
        $info = self::sendRequest('module/info', $params, 'GET');
        if ($info['code'] != 1) {
            throw new Exception($info['msg']);
        }
        return $info;
    }

    /**
     * 远程下载模块
     *
     * @param string $name   模块名称
     * @param array  $extend 扩展参数
     * @return  string
     */
    public static function download($name, $extend = [])
    {
        $modulesTempDir = self::getModulesBackupDir();
        $tmpFile = $modulesTempDir . $name . ".zip";

        try {
            $client = self::getClient();
            $response = $client->get('module/download', ['query' => array_merge(['name' => $name], $extend)]);
            $body = $response->getBody();
            $content = $body->getContents();
            if (substr($content, 0, 1) === '{') {
                $json = (array)json_decode($content, true);
                //如果传回的是一个下载链接,则再次下载
                if ($json['data'] && isset($json['data']['url']) && $json['data']['url']) {
                    $response = $client->get($json['data']['url']);
                    $body = $response->getBody();
                    $content = $body->getContents();
                } else {
                    $json_data = is_array($json['data']) ? $json['data'] : [];
                    //下载返回错误，抛出异常
                    throw new ModuleException($json['msg'], $json['code'], $json_data);
                }
            }
        } catch (TransferException $e) {
            throw new Exception("Module package download failed");
        }

        if ($write = fopen($tmpFile, 'w')) {
            fwrite($write, $content);
            fclose($write);
            return $tmpFile;
        }
        throw new Exception("No permission to write temporary files");
    }

    /**
     * 解压模块
     *
     * @param string $name 模块名称
     * @param string $file 文件路径
     * @return  string
     * @throws  Exception
     */
    public static function unzip($name, $file = '')
    {
        if (!$name) {
            throw new Exception('Invalid parameters');
        }
        $modulesBackupDir = self::getModulesBackupDir();
        $file = $file ?: $modulesBackupDir . $name . '.zip';

        // 打开模块压缩包
        $zip = new ZipFile();
        try {
            $zip->openFile($file);
        } catch (ZipException $e) {
            $zip->close();
            throw new Exception('Unable to open the zip file');
        }

        $dir = self::getModuleDir($name);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755);
        }

        // 解压模块压缩包
        try {
            $zip->extractTo($dir);
        } catch (ZipException $e) {
            throw new Exception('Unable to extract the file');
        } finally {
            $zip->close();
        }
        return $dir;
    }

    /**
     * 解压模块
     *
     * @param string $name 模块名称
     * @param string $file 文件路径
     * @param bool $conflict 检测冲突文件
     * @return  string
     * @throws  Exception
     */
    public static function upgradeUnzip($name, $file = '', $conflict = true)
    {
        if (!$name) {
            throw new Exception('Invalid parameters');
        }
        $modulesBackupDir = self::getModulesBackupDir();
        $file = $file ?: $modulesBackupDir . $name . '.zip';

        // 打开模块压缩包
        $zip = new ZipFile();
        try {
            $zip->openFile($file);
        } catch (ZipException $e) {
            $zip->close();
            throw new Exception('Unable to open the zip file');
        }

        $dir = self::getModuleDir($name);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755);
        }
        $conflictlist = [];
        $ignoreDirs = config('badouadmin.upgrade_ignore_dirs'); // 需忽略的目录前缀
        $extractlist = [];
        // 解压模块压缩包
        try {
            $fileList = $zip->getListFiles();
            foreach ($fileList as $file) {
                $destPath = $dir . $file;
                if ($ignoreDirs && self::isIgnoreDirs($ignoreDirs, $file)) {
                    continue;
                }
                // 需要解压的文件
                $extractlist[] = $file;
                //检测冲突文件
                $zipContent = $zip->getEntryContents($file);
                if ($conflict) {
                    if (is_file($destPath)) {
                        if (strlen($zipContent) != filesize($destPath) ||
    md5($zipContent) != md5_file($destPath)) {
                            $conflictlist[] =  $file;
                            continue;
                        }
                    }
                }
            }
            /* 备份差异文件 */
            if (count($conflictlist)) {
                $backupzip = new ZipFile();
                try {
                    foreach ($conflictlist as $k => $v) {
                        $backupzip->addFile($dir . $v, $v);
                    }
                    $modulesBackupDir = self::getModulesBackupDir();
                    $backupzip->saveAsFile($modulesBackupDir . $name . "-conflict-upgrade-" . date("YmdHis") . ".zip");
                } catch (Exception $e) {

                } finally {
                    $backupzip->close();
                }
            }
            // 解压模块压缩包
            foreach ($extractlist as $file) {
                $zip->extractTo($dir, $file);
            }
        } catch (ZipException $e) {
            throw new Exception('Unable to extract the file');
        } finally {
            $zip->close();
        }
        return $dir;
    }

    /**
     * 验证压缩包、依赖验证
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public static function valid($params = [])
    {
        $json = self::sendRequest('module/valid', $params, 'POST');
        if ($json && isset($json['code'])) {
            if ($json['code']) {
                return true;
            } else {
                throw new Exception($json['msg'] ?? "Invalid module package");
            }
        } else {
            throw new Exception("Unknown data format");
        }
    }

    /**
     * 备份模块
     * @param string $name 模块名称
     * @return bool
     * @throws Exception
     */
    public static function backup($name)
    {
        $modulesBackupDir = self::getModulesBackupDir();
        $file = $modulesBackupDir . $name . '-backup-' . date("YmdHis") . '.zip';
        $zipFile = new ZipFile();
        try {
            $zipFile
                ->addDirRecursive(self::getModuleDir($name))
                ->saveAsFile($file)
                ->close();
        } catch (ZipException $e) {

        } finally {
            $zipFile->close();
        }

        return true;
    }

    /**
     * 检测模块是否完整
     *
     * @param string $name 模块名称
     * @return  boolean
     * @throws  Exception
     */
    public static function check($name)
    {
        if (!$name || !is_dir(MODULE_PATH . $name)) {
            throw new Exception('Module not exists');
        }
        $moduleClass = self::getModuleClass($name);
        if (!$moduleClass) {
            throw new Exception("The module file does not exist");
        }

        $info = self::getModuleInfo($name);
        if (!$info) {
            throw new Exception("The configuration file content is incorrect");
        }
        return true;
    }

    /**
     * 是否有冲突
     *
     * @param string $name 模块名称
     * @return  boolean
     * @throws  ModuleException
     */
    public static function noconflict($name)
    {
        // 检测冲突文件
        $list = self::getGlobalFiles($name, true);
        if ($list) {
            //发现冲突文件，抛出异常
            throw new ModuleException(__("Conflicting file found"), -3, ['conflictlist' => $list]);
        }
        return true;
    }

    /**
     * 导入SQL
     *
     * @param string $name     模块名称
     * @param string $fileName SQL文件名称
     * @return  boolean
     */
    public static function importsql($name, $fileName = null)
    {
        $fileName = is_null($fileName) ? 'install.sql' : $fileName;
        $sqlFile = self::getModuleDir($name) . $fileName;
        if (is_file($sqlFile)) {
            $lines = file($sqlFile);
            $templine = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*') {
                    continue;
                }

                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $config = self::getDbConfig();
                    $templine = str_ireplace('__PREFIX__', $config['prefix'], $templine);
                    $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                    try {
                        Db::getPdo()->exec($templine);
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
     * 安装模块
     *
     * @param string  $name    模块名称
     * @param boolean $force   是否覆盖
     * @param array   $extend  扩展参数
     * @param array   $tmpFile 本地文件
     * @return  boolean
     * @throws  Exception
     * @throws  ModuleException
     */
    public static function install($name, $force = false, $extend = [], $tmpFile = '')
    {
        if (!$name || (is_dir(MODULE_PATH . $name) && !$force)) {
            throw new Exception('Module already exists');
        }

        $extend['domain'] = request()->host(true);

        // 远程下载模块
        $tmpFile = $tmpFile ?: Server::download($name, $extend);

        $moduleDir = self::getModuleDir($name);

        try {
            // 解压模块压缩包到模块目录
            Server::unzip($name, $tmpFile);

            // 检查模块是否完整
            Server::check($name);

            if (!$force) {
                Server::noconflict($name);
            }
        } catch (ModuleException $e) {
            Filesystem::delDir($moduleDir);
            throw new ModuleException($e->getMessage(), $e->getCode(), $e->getData());
        } catch (Exception $e) {
            Filesystem::delDir($moduleDir);
            throw new Exception($e->getMessage());
        } finally {
            // 移除临时文件
            @unlink($tmpFile);
        }

        // 默认启用该模块
        $info = self::getModuleInfo($name);

        Db::startTrans();
        try {
            if (!$info['state']) {
                $info['state'] = 1;
                self::getModuleInfo($name, $info);
            }

            // 执行安装脚本
            $class = self::getModuleClass($name);
            if (class_exists($class) && method_exists($class, 'install')) {
                $module = new $class();
                $module->install();
            }
            Db::commit();
        } catch (Exception $e) {
            Filesystem::delDir($moduleDir);
            Db::rollback();
            throw new Exception($e->getMessage());
        }

        // 导入
        Server::importsql($name);

        // 启用模块
        Server::enable($name, true);

        $info['testdata'] = is_file(Server::getTestdataFile($name));
        return $info;
    }

    /**
     * 卸载模块
     *
     * @param string  $name
     * @param boolean $force 是否强制卸载
     * @return  boolean
     * @throws  Exception
     */
    public static function uninstall($name, $force = false)
    {
        if (!$name || !is_dir(MODULE_PATH . $name)) {
            throw new Exception('Module not exists');
        }

        if (!$force) {
            Server::noconflict($name);
        }

        // 移除模块全局资源文件
        if ($force) {
            $list = Server::getGlobalFiles($name);
            foreach ($list as $k => $v) {
                @unlink(root_path() . $v);
            }
        }

        // 执行卸载脚本
        try {
            $class = self::getModuleClass($name);
            if (class_exists($class) && method_exists($class, 'uninstall')) {
                $module = new $class();
                $module->uninstall();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 移除模块目录
        Filesystem::delDir(MODULE_PATH . $name);

        return true;
    }

    /**
     * 启用
     * @param string  $name  模块名称
     * @param boolean $force 是否强制覆盖
     * @return  boolean
     */
    public static function enable($name, $force = false)
    {
        if (!$name || !is_dir(MODULE_PATH . $name)) {
            throw new Exception('Module not exists');
        }

        if (!$force) {
            Server::noconflict($name);
        }

        //备份冲突文件
        if (config('badouadmin.backup_global_files')) {
            $conflictFiles = self::getGlobalFiles($name, true);
            if ($conflictFiles) {
                $zip = new ZipFile();
                try {
                    foreach ($conflictFiles as $k => $v) {
                        $zip->addFile(root_path() . $v, $v);
                    }
                    $modulesBackupDir = self::getModulesBackupDir();
                    $zip->saveAsFile($modulesBackupDir . $name . "-conflict-enable-" . date("YmdHis") . ".zip");
                } catch (Exception $e) {

                } finally {
                    $zip->close();
                }
            }
        }

        $moduleDir = self::getModuleDir($name);
        $sourceAssetsDir = self::getSourceAssetsDir($name);
        $destAssetsDir = self::getDestAssetsDir($name);

        $files = self::getGlobalFiles($name);
        if ($files) {
            //刷新模块配置缓存
            Server::config($name, ['files' => $files]);
        }

        // 复制文件
        if (is_dir($sourceAssetsDir)) {
            Filesystem::copydirs($sourceAssetsDir, $destAssetsDir);
        }

        // 复制app和public到全局
        foreach (self::getCheckDirs() as $k => $dir) {
            if (is_dir($moduleDir . $dir)) {
                Filesystem::copydirs($moduleDir . $dir, root_path() . $dir);
            }
        }

        // 删除模块目录已复制到全局的文件
        Filesystem::delDir($sourceAssetsDir);
        foreach (self::getCheckDirs() as $k => $dir) {
            Filesystem::delDir($moduleDir . $dir);
        }

        //执行启用脚本
        try {
            $class = self::getModuleClass($name);
            if (class_exists($class)) {
                $module = new $class();
                if (method_exists($class, "enable")) {
                    $module->enable(['force' => $force]);
                }
            }
        } catch (ModuleException $e) {
            throw new ModuleException($e->getMessage(), $e->getCode(), $e->getData());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $info = self::getModuleInfo($name);
        $info['state'] = 1;
        unset($info['url']);
        self::setModuleInfo($name, $info);

        // 刷新
        self::refresh();
        return true;
    }

    /**
     * 禁用
     *
     * @param string  $name  模块名称
     * @param boolean $force 是否强制禁用
     * @return  boolean
     * @throws  Exception
     */
    public static function disable($name, $force = false)
    {
        if (!$name || !is_dir(MODULE_PATH . $name)) {
            throw new Exception('Module not exists');
        }

        if (!$force) {
            Server::noconflict($name);
        }

        if (config('badouadmin.backup_global_files')) {
            //仅备份修改过的文件
            $conflictFiles = Server::getGlobalFiles($name, true);
            if ($conflictFiles) {
                $zip = new ZipFile();
                try {
                    foreach ($conflictFiles as $k => $v) {
                        $zip->addFile(root_path() . $v, $v);
                    }
                    $modulesBackupDir = self::getModulesBackupDir();
                    $zip->saveAsFile($modulesBackupDir . $name . "-conflict-disable-" . date("YmdHis") . ".zip");
                } catch (Exception $e) {

                } finally {
                    $zip->close();
                }
            }
        }

        $config = Server::config($name);

        $moduleDir = self::getModuleDir($name);
        //模块资源目录
        $destAssetsDir = self::getDestAssetsDir($name);

        // 移除模块全局文件
        $list = Server::getGlobalFiles($name);

        //当无法获取全局文件列表时也将列表复制回模块目录
        if (!$list) {
            if ($config && isset($config['files']) && is_array($config['files'])) {
                foreach ($config['files'] as $index => $item) {
                    //避免切换不同服务器后导致路径不一致
                    $item = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $item);
                    //模块资源目录，无需重复复制
                    if (stripos($item, str_replace(root_path(), '', $destAssetsDir)) === 0) {
                        continue;
                    }
                    //检查目录是否存在，不存在则创建
                    $itemBaseDir = dirname($moduleDir . $item);
                    if (!is_dir($itemBaseDir)) {
                        @mkdir($itemBaseDir, 0755, true);
                    }
                    if (is_file(root_path() . $item)) {
                        @copy(root_path() . $item, $moduleDir . $item);
                    }
                }
                $list = $config['files'];
            }
            //复制模块目录资源
            if (is_dir($destAssetsDir)) {
                Filesystem::copydirs($destAssetsDir, $moduleDir . 'assets' . DIRECTORY_SEPARATOR);
            }
        }

        $dirs = [];
        foreach ($list as $k => $v) {
            $file = root_path() . $v;
            $dirs[] = dirname($file);
            @unlink($file);
        }

        // 移除模块空目录
        $dirs = array_filter(array_unique($dirs));
        foreach ($dirs as $k => $v) {
            Filesystem::delEmptyDir($v);
        }

        $info = self::getModuleInfo($name);
        $info['state'] = 0;
        unset($info['url']);

        self::setModuleInfo($name, $info);

        // 执行禁用脚本
        try {
            $class = self::getModuleClass($name);
            if (class_exists($class)) {
                $module = new $class();

                if (method_exists($class, "disable")) {
                    $module->disable();
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 刷新
        self::refresh();
        return true;
    }

    /**
     * 升级模块
     *
     * @param string $name   模块名称
     * @param array  $extend 扩展参数
     */
    public static function upgrade($name, $extend = [], $tmpFile = false)
    {
        $info = self::getModuleInfo($name);
        if ($info['state'] == 1) {
            throw new Exception(__('Please disable module first'));
        }

        // 远程下载模块(如果为本地文件则使用本地文件)
        $tmpFile = $tmpFile ? $tmpFile : Server::download($name, $extend);

        // 备份模块文件
        Server::backup($name);

        $moduleDir = self::getModuleDir($name);

        try {
            // 解压模块
            Server::upgradeUnzip($name, $tmpFile);
        } catch (ModuleException $e) {
            throw new ModuleException($e->getMessage(), $e->getCode(), $e->getData());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } finally {
            // 移除临时文件
            @unlink($tmpFile);
        }

        // 导入
        Server::importsql($name);

        // 执行升级脚本
        try {
            $moduleName = ucfirst($name);
            //创建临时类用于调用升级的方法
            $sourceFile = $moduleDir . $moduleName . ".php";
            $destFile = $moduleDir . $moduleName . "Upgrade.php";

            $classContent = str_replace("class {$moduleName} extends", "class {$moduleName}Upgrade extends", file_get_contents($sourceFile));

            //创建临时的类文件
            file_put_contents($destFile, $classContent);

            $className = "\\modules\\" . $name . "\\" . $moduleName ;
            $module = new $className($name);

            //调用升级的方法
            if (method_exists($module, "upgrade")) {
                $module->upgrade();
            }

            //移除临时文件
            @unlink($destFile);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        //必须变更版本号
        $info['version'] = $extend['version'] ?? $info['version'];
        return $info;
    }

    /**
     * 读取或修改模块配置
     * @param string $name
     * @param array  $changed
     * @return array
     */
    public static function config($name, $changed = [])
    {
        $moduleDir = self::getModuleDir($name);
        $moduleConfigFile = $moduleDir . '.modulerc';
        $config = [];
        if (is_file($moduleConfigFile)) {
            $config = (array)json_decode(file_get_contents($moduleConfigFile), true);
        }
        $config = array_merge($config, $changed);
        if ($changed) {
            file_put_contents($moduleConfigFile, json_encode($config, JSON_UNESCAPED_UNICODE));
        }
        return $config;
    }

    /**
     * 获取模块在全局的文件
     *
     * @param string  $name         模块名称
     * @param boolean $onlyconflict 是否只返回冲突文件
     * @return  array
     */
    public static function getGlobalFiles($name, $onlyconflict = false)
    {
        $list = [];
        $moduleDir = self::getModuleDir($name);
        $checkDirList = self::getCheckDirs();
        $checkDirList = array_merge($checkDirList, ['assets']);

        $assetDir = self::getDestAssetsDir($name);

        // 扫描模块目录是否有覆盖的文件
        foreach ($checkDirList as $k => $dirName) {
            //检测目录是否存在
            if (!is_dir($moduleDir . $dirName)) {
                continue;
            }
            //匹配出所有的文件
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($moduleDir . $dirName, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                if ($fileinfo->isFile()) {
                    $filePath = $fileinfo->getPathName();
                    //如果名称为assets需要做特殊处理
                    if ($dirName === 'assets') {
                        $path = str_replace(root_path(), '', $assetDir) . str_replace($moduleDir . $dirName . DIRECTORY_SEPARATOR, '', $filePath);
                    } else {
                        $path = str_replace($moduleDir, '', $filePath);
                    }
                    if ($onlyconflict) {
                        $destPath = root_path() . $path;
                        if (is_file($destPath)) {
                            if (filesize($filePath) != filesize($destPath) || md5_file($filePath) != md5_file($destPath)) {
                                $list[] = $path;
                            }
                        }
                    } else {
                        $list[] = $path;
                    }
                }
            }
        }
        $list = array_filter(array_unique($list));
        return $list;
    }

    /**
     * 更新本地应用模块授权
     */
    public static function authorization($params = [])
    {
        $moduleList = self::getInstalldModuleList();
        $result = [];
        $domain = request()->host(true);
        $modules = [];
        foreach ($moduleList as $name => $item) {
            $config = self::config($name);
            $modules[] = ['name' => $name, 'domains' => $config['domains'] ?? [], 'licensecodes' => $config['licensecodes'] ?? [], 'validations' => $config['validations'] ?? []];
        }
        $params = array_merge($params, [
            'faversion' => config('badouadmin.version'),
            'domain'    => $domain,
            'modules'    => $modules
        ]);
        $result = self::sendRequest('module/authorization', $params, 'POST');
        if (isset($result['code']) && $result['code'] == 1) {
            $json = $result['data']['modules'] ?? [];
            foreach ($moduleList as $name => $item) {
                self::config($name, ['domains' => $json[$name]['domains'] ?? [], 'licensecodes' => $json[$name]['licensecodes'] ?? [], 'validations' => $json[$name]['validations'] ?? []]);
            }
            return true;
        } else {
            throw new Exception($result['msg'] ?? __('Network error'));
        }
    }

    /**
     * 验证模块授权，应用模块需要授权使用，移除或绕过授权验证，保留追究法律责任的权利
     * @param $name
     * @return bool
     */
    public static function checkModuleAuthorization($name)
    {
        $request = request();
        $config = self::config($name);
        $domain = self::getRootDomain($request->host(true));
        //应用模块需要授权使用，移除或绕过授权验证，保留追究法律责任的权利
        if (isset($config['domains']) && isset($config['validations']) && isset($config['licensecodes'])) {
            $index = array_search($domain, $config['domains']);
            if ((in_array($domain, $config['domains']) && in_array(md5(md5($domain) . ($config['licensecodes'][$index] ?? '')), $config['validations'])) || $request->isCli()) {
                $request->param(['authorized' => $domain ?: 'cli']);
                return true;
            } elseif ($config['domains']) {
                foreach ($config['domains'] as $index => $item) {
                    if (substr_compare($domain, "." . $item, -strlen("." . $item)) === 0 && in_array(md5(md5($item) . ($config['licensecodes'][$index] ?? '')), $config['validations'])) {
                        $request->param(['authorized' => $domain ?: 'cli']);
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * 获取顶级域名
     * @param $domain
     * @return string
     */
    public static function getRootDomain($domain)
    {
        $host = strtolower(trim($domain));
        $hostArr = explode('.', $host);
        $hostCount = count($hostArr);
        $cnRegex = '/\w+\.(gov|org|ac|mil|net|edu|com|bj|tj|sh|cq|he|sx|nm|ln|jl|hl|js|zj|ah|fj|jx|sd|ha|hb|hn|gd|gx|hi|sc|gz|yn|xz|sn|gs|qh|nx|xj|tw|hk|mo)\.cn$/i';
        $countryRegex = '/\w+\.(\w{2}|com|net)\.\w{2}$/i';
        if ($hostCount > 2 && (preg_match($cnRegex, $host) || preg_match($countryRegex, $host))) {
            $host = implode('.', array_slice($hostArr, -3, 3, true));
        } else {
            $host = implode('.', array_slice($hostArr, -2, 2, true));
        }
        return $host;
    }

    /**
     * 获取模块类的类名
     * @param string $uid
     * @param string $type
     * @param mixed $class
     * @return string
     */
    public static function getModuleClass(string $name, string $type = 'event', ?string $class = null): string
    {
        $name = parse_name($name);
        if (!is_null($class) && strpos($class, '.')) {
            $class                    = explode('.', $class);
            $class[count($class) - 1] = parse_name(end($class), 1);
            $class                    = implode('\\', $class);
        } else {
            $class = parse_name(is_null($class) ? $name : $class, 1);
        }
        $namespace = match ($type) {
            'controller' => '\\modules\\' . $name . '\\controller\\' . $class,
            default => '\\modules\\' . $name . '\\' . $class,
        };
        return class_exists($namespace) ? $namespace : '';
    }

    /**
     * 获取插件创建的表
     * @param string $name 插件名
     * @return array
     */
    public static function getModuleTables($name)
    {
        $moduleInfo = self::getModuleInfo($name);
        if (!$moduleInfo) {
            return [];
        }
        $regex = "/^CREATE\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z_]+)`?/mi";
        $sqlFile = MODULE_PATH . $name . DS . 'install.sql';
        $tables = [];
        if (is_file($sqlFile)) {
            preg_match_all($regex, file_get_contents($sqlFile), $matches);
            if ($matches && isset($matches[2]) && $matches[2]) {
                $dbconfig = self::getDbConfig();
                $prefix = $dbconfig['prefix'];
                $tables = array_map(function ($item) use ($prefix) {
                    return str_replace("__PREFIX__", $prefix, $item);
                }, $matches[2]);
            }
        }
        return $tables;
    }

    /**
     * 获取模块的bootstrap.stub
     * @return string
     */
    public static function getBootstrapFile($name)
    {
        return MODULE_PATH . $name . DIRECTORY_SEPARATOR . 'bootstrap.stub';
    }

    /**
     * 获取testdata.sql路径
     * @return string
     */
    public static function getTestdataFile($name)
    {
        return MODULE_PATH . $name . DIRECTORY_SEPARATOR . 'testdata.sql';
    }

    /**
     * 获取指定模块的目录
     */
    public static function getModuleDir($name)
    {
        $dir = MODULE_PATH . $name . DIRECTORY_SEPARATOR;
        return $dir;
    }

    /**
     * 获取模块备份目录
     */
    public static function getModulesBackupDir()
    {
        $dir = root_path().'runtime'.DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * 获取模块源资源文件夹
     * @param string $name 模块名称
     * @return  string
     */
    protected static function getSourceAssetsDir($name)
    {
        return MODULE_PATH . $name .DIRECTORY_SEPARATOR.'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR. $name . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取模块目标资源文件夹
     * @param string $name 模块名称
     * @return  string
     */
    protected static function getDestAssetsDir($name)
    {
        $assetsDir = root_path() . str_replace("/", DIRECTORY_SEPARATOR, "public/assets/{$name}/");
        return $assetsDir;
    }

    /**
     * 获取远程服务器
     * @return  string
     */
    protected static function getServerUrl()
    {
        return config('badouadmin.api_url');
    }

    /**
     * 获取检测的全局文件夹目录
     * @return  array
     */
    protected static function getCheckDirs()
    {
        return [
            'app',
            'public',
            'template',
            'public/modules'
        ];
    }

    /**
     * 获取请求对象
     * @return Client
     */
    /**
     * 获取请求对象
     * @return Client
     */
    public static function getClient()
    {
        $options = [
            'base_uri'        => self::getServerUrl(),
            'timeout'         => 30,
            'connect_timeout' => 30,
            'verify'          => false,
            'http_errors'     => false,
            'headers'         => [
                'X-REQUESTED-WITH' => 'XMLHttpRequest',
                'Referer'          => dirname(request()->root(true)),
                'User-Agent'       => 'BadouCMSClient',
            ]
        ];
        static $client;
        if (empty($client)) {
            $client = new Client($options);
        }
        return $client;
    }

    /**
     * 发送请求
     * @return array
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function sendRequest($url, $params = [], $method = 'POST')
    {
        $json = [];
        try {
            $client = self::getClient();
            $options = strtoupper($method) == 'POST' ? ['form_params' => $params] : ['query' => $params];

            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $content = $body->getContents();
            $json = (array)json_decode($content, true);
        } catch (TransferException $e) {
            throw new Exception(__('Network error'));
        } catch (\Exception $e) {
            throw new Exception(__('Unknown data format'));
        }
        return $json;
    }

    /**
     * 匹配配置文件中info信息
     * @param ZipFile $zip
     * @return array|false
     * @throws Exception
     */
    protected static function getInfoIni($zip)
    {
        $config = [];
        // 读取模块信息
        try {
            $info = $zip->getEntryContents('info.ini');
            $config = parse_ini_string($info);
        } catch (ZipException $e) {
            throw new Exception('Unable to extract the file');
        }
        return $config;
    }

    public static function getModuleInfo($name, $force = false)
    {
        if (!$force) {
            $info = Config::get($name);
            if ($info) {
                return $info;
            }
        }
        $info = [];
        $modulePath = self::getModuleDir($name);
        $infoFile = $modulePath . 'info.ini';
        if (is_file($infoFile)) {
            $info = Config::load($infoFile, $name);
        }
        Config::set($info, $name);

        return $info ? $info : [];
    }

    /**
     * 设置模块ini
     * @param string $dir 模块目录路径
     * @param array  $arr 新的ini数据
     * @return bool
     */
    public static function setModuleInfo($name, $arr)
    {
        $dir      = self::getModuleDir($name);
        $infoFile = $dir . 'info.ini';
        $ini      = [];
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $ini[] = "[$key]";
                foreach ($val as $ikey => $ival) {
                    $ini[] = "$ikey = $ival";
                }
            } else {
                $ini[] = "$key = $val";
            }
        }

        if (!file_put_contents($infoFile, implode("\n", $ini) . "\n", LOCK_EX)) {
            throw new Exception("Configuration file has no write permission");
        }
        return true;
    }

    /**
     * 刷新插件缓存文件
     *
     * @return  boolean
     * @throws  Exception
     */
    public static function refresh()
    {
        $modules = self::getInstalldModuleList();
        $bootstrapArr = [];
        foreach ($modules as $name => $module) {
            $bootstrapFile = self::getBootstrapFile($name);
            if ($module['state'] && is_file($bootstrapFile)) {
                $bootstrapArr[] = file_get_contents($bootstrapFile);
            }
        }
        $moduleFile = app_path(). str_replace("/", DS, "view/common/modules.html");
        if ($handle = fopen($moduleFile, 'w')) {
            fwrite($handle, implode("\n", $bootstrapArr));
            fclose($handle);
        } else {
            throw new Exception(__("Unable to open file '%s' for writing", ["modules.html"]));
        }

        return true;
    }

    /**
     * 忽略匹配
     * @param string $pattern  规则
     * @param string $string   字符串
     * @return bool
     */
    public static function ignoreMatch($pattern, $string)
    {
        $pattern   = str_replace('\\', '/', $pattern);
        $pattern   = str_replace('.', '\.', $pattern);
        $firstChar = substr($pattern, 0, 1);
        if ($firstChar == '!') {
            $pattern = '#' . substr($pattern, 1) . '#us';
            return preg_match($pattern, $string) !== 1;
        } elseif ($firstChar === '/') {
            $pattern = '#^' . substr($pattern, 1) . '#us';
        } else {
            $pattern = '#' . $pattern . '#us';
        }
        return preg_match($pattern, $string) === 1;
    }

    //是否忽略目录
    protected static function isIgnoreDirs($ignoreDirs, $relativePath)
    {
        foreach ($ignoreDirs as $token) {
            if (static::ignoreMatch($token, $relativePath)) {
                return true;
            }
        }

        return false;
    }
}

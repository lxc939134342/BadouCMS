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

namespace bd\service\install;

use ba\Random;
use ba\Version;
use ba\Filesystem;
use think\Exception;
use think\facade\Db;
use think\facade\View;
use think\facade\Config;
use think\facade\Request;
use think\db\exception\PDOException;
use app\admin\model\Admin as AdminModel;
use app\admin\model\User as UserModel;

class Install
{
    protected $installPath;
    protected $app;
    protected $view;
    protected $request;
    /**
     * 需要的依赖版本
     */
    public static array $needDependentVersion = [
        'php'  => '8.0.2',
    ];
    /**
     * 配置文件
     */
    public static string $dbConfigFileName    = 'database.php';
    public static string $buildConfigFileName = 'buildadmin.php';
    /**
     * 安装完成标记
     * 配置完成则建立lock文件
     * 执行命令成功执行再写入标记到lock文件
     * 实现命令执行失败，重载页面可重新执行
     */
    public static string $InstallationCompletionMark = 'install-end';

    /**
     * 安装锁文件名称
     */
    public static string $lockFileName = 'install.lock';

    public function __construct()
    {
        $this->installPath = root_path() . 'extend' . DIRECTORY_SEPARATOR . 'bd' . DIRECTORY_SEPARATOR  . 'service' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR;
        $this->app = app();
    }

    /**
     * 安装
     */
    public function index()
    {
        $this->view = View::instance();
        $this->request = Request::instance();

        // 加载控制器语言包
        $this->app->lang->load([
            $this->installPath . 'zh-cn.php'
        ]);

        $installLockFile = public_path() . "install.lock";

        if (is_file($installLockFile)) {
            echo __('The system has been installed. If you need to reinstall, please remove %s first', ['install.lock']);
            exit;
        }
        $output = function ($code, $msg, $url = null, $data = null) {
            return json(['code' => $code, 'msg' => $msg, 'url' => $url, 'data' => $data]);
        };

        if ($this->request->isPost()) {
            $param = $this->request->only(['type', 'adminname', 'adminpassword', 'adminPasswordConfirmation', 'sitename']);
            try {
                if ($param['adminpassword'] !== $param['adminPasswordConfirmation']) {
                    return $output(0, __('The two passwords you entered did not match'));
                }

                $this->baseConfig();
                // 管理员配置入库
                $adminModel             = new AdminModel();
                $defaultAdmin           = $adminModel->where('username', 'admin')->find();
                $defaultAdmin->username = $param['adminname'];
                $defaultAdmin->nickname = ucfirst($param['adminname']);
                $defaultAdmin->save();

                if (isset($param['adminpassword']) && $param['adminpassword']) {
                    $adminModel->resetPassword($defaultAdmin->id, $param['adminpassword']);
                }

                // 默认用户密码修改
                $user = new UserModel();
                $user->resetPassword(1, Random::build());
                // 修改站点名称
                \app\admin\model\Config::where('name', 'site_name')->update([
                    'value' => $param['sitename']
                ]);


            } catch (\Exception $e) {
                return $output(0, $e->getMessage());
            }
            return $output(1, __('Install Successed'), null, ['adminName' => 'web/index.html']);
        }
        $errInfo = '';
        try {
            $this->checkenv();
        } catch (\Exception $e) {
            $errInfo = $e->getMessage();
        }

        return $this->view->fetch($this->installPath . "install.html", ['errInfo' => $errInfo]);
    }


    /**
     * 系统基础配置
     * post请求=开始安装
     */
    public function baseConfig(): void
    {
        if ($this->isInstallComplete()) {
            throw new Exception(__('The system has completed installation. If you need to reinstall, please delete the %s file first', ['public/' . self::$lockFileName]));
        }

        $connectData = $databaseParam = $this->request->only(['hostname', 'username', 'password', 'hostport', 'database', 'prefix']);

        // 数据库配置测试
        $connectData['database'] = '';
        $connect                 = $this->connectDb($connectData, true);
        if ($connect['code'] == 0) {
            throw new Exception($connect['msg']);
        }

        // 建立数据库
        if (!in_array($databaseParam['database'], $connect['databases'])) {
            $sql = "CREATE DATABASE IF NOT EXISTS `{$databaseParam['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $connect['pdo']->exec($sql);
        }

        $sql = file_get_contents($this->installPath . 'badoucms.sql');

        $sql = str_replace("`bd_", "`{$databaseParam['prefix']}", $sql);
        // 先尝试能否自动创建数据库
        try {
            $dbConfig                         = Config::get('database');
            $dbConfig['connections']['mysql'] = array_merge($dbConfig['connections']['mysql'], $databaseParam);
            Config::set(['connections' => $dbConfig['connections']], 'database');
            $connect = Db::connect('mysql', true);
            // 连接install命令中指定的数据库
            // 查询一次SQL,判断连接是否正常
            $connect->execute("SELECT 1");
            // 调用原生PDO对象进行批量查询
            $connect->getPdo()->exec($sql);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage());
        }

        // 写入数据库配置文件
        $dbConfigFile    = config_path() . self::$dbConfigFileName;
        $dbConfigContent = @file_get_contents($dbConfigFile);
        $callback        = function ($matches) use ($databaseParam) {
            $value = $databaseParam[$matches[1]] ?? '';
            return "'$matches[1]'$matches[2]=>$matches[3]env('database.$matches[1]', '$value'),";
        };
        $dbConfigText    = preg_replace_callback("/'(hostname|database|username|password|hostport|prefix)'(\s+)=>(\s+)env\('database\.(.*)',\s+'(.*)'\),/", $callback, $dbConfigContent);
        $result          = @file_put_contents($dbConfigFile, $dbConfigText);
        if (!$result) {
            throw new Exception(__('File has no write permission:%s', ['config/' . self::$dbConfigFileName]));
        }

        // 写入.env-example文件
        $envFile        = root_path() . '.env-example';
        $envFileContent = @file_get_contents($envFile);
        if ($envFileContent) {
            $databasePos = stripos($envFileContent, '[DATABASE]');
            if ($databasePos !== false) {
                // 清理已有数据库配置
                $envFileContent = substr($envFileContent, 0, $databasePos);
            }
            $envFileContent .= "\n" . '[DATABASE]' . "\n";
            $envFileContent .= 'TYPE = mysql' . "\n";
            $envFileContent .= 'HOSTNAME = ' . $databaseParam['hostname'] . "\n";
            $envFileContent .= 'DATABASE = ' . $databaseParam['database'] . "\n";
            $envFileContent .= 'USERNAME = ' . $databaseParam['username'] . "\n";
            $envFileContent .= 'PASSWORD = ' . $databaseParam['password'] . "\n";
            $envFileContent .= 'HOSTPORT = ' . $databaseParam['hostport'] . "\n";
            $envFileContent .= 'PREFIX = ' . $databaseParam['prefix'] . "\n";
            $envFileContent .= 'CHARSET = utf8mb4' . "\n";
            $envFileContent .= 'DEBUG = true' . "\n";
            $result         = @file_put_contents($envFile, $envFileContent);
            if (!$result) {
                throw new Exception(__('File has no write permission:%s', ['/' . $envFile]));
            }
        }

        // 设置新的Token随机密钥key
        $oldTokenKey        = Config::get('buildadmin.token.key');
        $newTokenKey        = Random::build('alnum', 32);
        $buildConfigFile    = config_path() . self::$buildConfigFileName;
        $buildConfigContent = @file_get_contents($buildConfigFile);
        $buildConfigContent = preg_replace("/'key'(\s+)=>(\s+)'$oldTokenKey'/", "'key'\$1=>\$2'$newTokenKey'", $buildConfigContent);
        $result             = @file_put_contents($buildConfigFile, $buildConfigContent);
        if (!$result) {
            throw new Exception(__('File has no write permission:%s', ['config/' . self::$buildConfigFileName]));
        }

        // 建立安装锁文件
        $result = @file_put_contents(public_path() . self::$lockFileName, date('Y-m-d H:i:s'));
        if (!$result) {
            throw new Exception(__('File has no write permission:%s', ['public/' . self::$lockFileName]));
        }
    }

    /**
     * 数据库连接-获取数据表列表
     * @param array $database
     * @param bool  $returnPdo
     * @return array
     */
    private function connectDb(array $database, bool $returnPdo = false): array
    {
        try {
            $dbConfig                         = Config::get('database');
            $dbConfig['connections']['mysql'] = array_merge($dbConfig['connections']['mysql'], $database);
            Config::set(['connections' => $dbConfig['connections']], 'database');

            $connect = Db::connect('mysql');
            $connect->execute("SELECT 1");
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            return [
                'code' => 0,
                'msg'  => __('Database connection failed:%s', [mb_convert_encoding($errorMsg ?: 'unknown', 'UTF-8', 'UTF-8,GBK,GB2312,BIG5')])
            ];
        }

        $databases = [];
        // 不需要的数据表
        $databasesExclude = ['information_schema', 'mysql', 'performance_schema', 'sys'];
        $res              = $connect->query("SHOW DATABASES");
        foreach ($res as $row) {
            if (!in_array($row['Database'], $databasesExclude)) {
                $databases[] = $row['Database'];
            }
        }

        return [
            'code'      => 1,
            'msg'       => '',
            'databases' => $databases,
            'pdo'       => $returnPdo ? $connect->getPdo() : '',
        ];
    }

    /**
     * 检测环境
     */
    protected function checkenv()
    {
        if ($this->isInstallComplete()) {
            throw new Exception(__('The system has completed installation. If you need to reinstall, please delete the %s file first', ['public/' . self::$lockFileName]));
        }
        if (env('database.type')) {
            throw new Exception(__('The .env file with database configuration was detected. Please clean up and try again!'));
        }

        // php版本-start
        $phpVersion        = phpversion();
        $phpVersionCompare = Version::compare(self::$needDependentVersion['php'], $phpVersion);
        if (!$phpVersionCompare) {
            throw new Exception(__("The current version %s is too low, please use PHP %s or higher", [PHP_VERSION, self::$needDependentVersion['php']]));
        }
        // php版本-end

        // 配置文件-start
        $dbConfigFile     = config_path() . self::$dbConfigFileName;
        $configIsWritable = Filesystem::pathIsWritable(config_path()) && Filesystem::pathIsWritable($dbConfigFile);
        if (!$configIsWritable) {
            throw new Exception(__('The current permissions are insufficient to write the configuration file config/database.php'));
        }
        // 配置文件-end

        // public-start
        $publicIsWritable = Filesystem::pathIsWritable(public_path());
        if (!$publicIsWritable) {
            throw new Exception(__("public directory is not writable"));
        }
        // public-end

        // PDO-start
        $phpPdo = extension_loaded("PDO") && extension_loaded('pdo_mysql');
        if (!$phpPdo) {
            throw new Exception(__("PDO is not currently installed and cannot be installed"));
        }
        // PDO-end

        // GD2和freeType-start
        $phpGd2 = extension_loaded('gd') && function_exists('imagettftext');
        if (!$phpGd2) {
            throw new Exception(__("gd2 is not currently installed and cannot be installed"));
        }
        // GD2和freeType-end

        // proc_open
        $phpProc = function_exists('proc_open') && function_exists('proc_close') && function_exists('proc_get_status');
        if (!$phpProc) {
            throw new Exception(__("proc_open is not currently installed and cannot be installed"));
        }
        // proc_open-end

        // 检测目录是否存在
        $checkDirs = [
            'vendor',
            'vendor'.DIRECTORY_SEPARATOR.'topthink',
        ];

        foreach ($checkDirs as $k => $v) {
            if (!is_dir(root_path() . $v)) {
                throw new Exception(__('Please go to the official website to download the full package or resource package and try to install'));
            }
        }
        return true;
    }

    protected function isInstallComplete(): bool
    {
        if (is_file(public_path() . self::$lockFileName)) {
            $contents = @file_get_contents(public_path() . self::$lockFileName);
            if ($contents == self::$InstallationCompletionMark) {
                return true;
            }
        }
        return false;
    }
}

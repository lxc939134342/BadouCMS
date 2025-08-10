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

namespace modules\upgrade;

use badou\Server;
use PDOException;
use PhpZip\ZipFile;
use think\Exception;
use think\facade\Db;
use badou\Filesystem;
use think\facade\Event;
use think\facade\Config;
use badou\ModuleException;
use app\common\library\Menu;
use app\admin\model\AdminRule;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Upgrade
{
    protected $dirs = [
        'app',
        'config',
        'public',
        'route',
        'extend',
        'vendor'
    ];
    public function appInit()
    {
        if (app('http')->getName() == 'admin') {
            // admin 头部添加
            Event::listen('admin_top_begin', function ($data) {
                return  '<li class="layui-nav-item layui-hide-xs upgrade">
                <a href="javascript:;" class="layui-badge-rim ">'.config('badouadmin.version').'</a>
            </li>';
            });
        }
    }

    public function enable($params = [])
    {
        $moduleDir = Server::getModuleDir('upgrade').DIRECTORY_SEPARATOR.'up'.DIRECTORY_SEPARATOR;
        $infoFile =  $moduleDir.'config'.DIRECTORY_SEPARATOR.'badouadmin.php';
        if (!is_file($infoFile)) {
            return true;
        }

        $info = include_once $infoFile;
        if ($info['version'] == config('badouadmin.version')) {
            return true;
        }

        // 备份冲突文件
        $conflictFiles = $this->getGlobalFiles($moduleDir, true);
        if ($conflictFiles) {
            if (isset($params['force']) && !$params['force']) {
                throw new ModuleException(__("Conflicting file found"), -3, ['conflictlist' => $conflictFiles]);
            }
            $zip = new ZipFile();
            try {
                foreach ($conflictFiles as $k => $v) {
                    $zip->addFile(root_path() . $v, $v);
                }
                $modulesBackupDir =  Server::getModulesBackupDir();
                $zip->saveAsFile($modulesBackupDir . "badouadmin-conflict-enable-" . date("YmdHis") . ".zip");
            } catch (Exception $e) {

            } finally {
                $zip->close();
            }
        }

        // 复制文件到全局
        foreach ($this->dirs as $k => $dir) {
            if (is_dir($moduleDir . $dir)) {
                Filesystem::copydirs($moduleDir . $dir, root_path() . $dir);
            }
        }

        // 删除模块目录已复制到全局的文件
        foreach ($this->dirs as $k => $dir) {
            Filesystem::delDir($moduleDir . $dir);
        }

        // 修改版本号
        $configFile = root_path() . 'config' . DIRECTORY_SEPARATOR . 'badouadmin.php';
        $config = file_get_contents($configFile);
        $config = str_replace("'version' => '" . config('badouadmin.version') . "'", "'version' => '" . $info['version'] . "'", $config);
        file_put_contents($configFile, $config);
    }

    /**
     * 获取模块在全局的文件
     *
     * @param string  $moduleDir        模块路径
     * @param boolean $onlyconflict 是否只返回冲突文件
     * @return  array
     */
    protected function getGlobalFiles($moduleDir, $onlyconflict = false)
    {
        $list = [];

        // 扫描模块目录是否有覆盖的文件
        foreach ($this->dirs as $k => $dirName) {
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
                    $path = str_replace($moduleDir, '', $filePath);
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
     * 插件升级方法
     */
    public function upgrade()
    {
        $menu = include_once __DIR__ . '/menu.php';
        $oldmenuname = [
            'dashboard',
            'general',
            'auth',
            'user',
            'module'
        ];
        $ids = [];
        foreach ($oldmenuname as $key => $value) {
            $ids = array_merge($ids, Menu::getAdminRuleIdsByName($value));
        }

        $old = AdminRule::where('id', 'in', $ids)->select();
        $old = $old->toArray();
        $old = array_column($old, null, 'name');

        $config_group = Db::name('config')->where('name', 'config_group')->value('value');
        $config_group = json_decode($config_group, true);
        $config_group_key = array_column($config_group, null, 'key');
        if (!in_array('user', $config_group_key)) {
            $config_group[] = [
                'key' => 'user',
                'name' => '会员配置',
            ];
            Db::name('config')->where('name', 'config_group')->update(['value' => json_encode($config_group)]);
        }

        $this->importsql('upgrade');

        Db::startTrans();
        try {
            foreach ($menu as $value) {
                Menu::menuUpdate([$value], $old, $value['parent_name']);
            }

            $ids = [];
            foreach ($old as $index => $item) {
                if (!isset($item['keep'])) {
                    $ids[] = $item['id'];
                }
            }
            if ($ids) {
                $where = ['id' => ['in', $ids]];
                AdminRule::where($where)->delete();
            }

            Db::commit();
        } catch (PDOException $e) {
            Db::rollback();
            return false;
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
    protected function importsql($name, $fileName = null)
    {
        $fileName = is_null($fileName) ? 'install.sql' : $fileName;
        $sqlFile = Server::getModuleDir($name) . $fileName;
        if (is_file($sqlFile)) {
            $lines = file($sqlFile);
            $templine = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*') {
                    continue;
                }

                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $dbConfig         = Config::get('database');
                    $config = $dbConfig['connections'][$dbConfig['default']];
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
}

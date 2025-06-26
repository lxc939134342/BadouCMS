<?php

namespace app\admin\controller;

use badou\ModuleException;
use badou\Server;
use PhpZip\ZipFile;
use think\Exception;
use badou\Filesystem;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use app\common\controller\Backend;
use PhpZip\Exception\ZipException;

class Upgrade extends Backend
{
    protected $dirs = [
        'app',
        'config',
        'public',
        'route',
        'extend',
        'template'
    ];
    public function upgrade()
    {
        // 是否强制覆盖
        $force = $this->request->param('force', 0);

        try {
            $moduleDir = Server::getModuleDir('upgrade').DIRECTORY_SEPARATOR.'up'.DIRECTORY_SEPARATOR;
            $infoFile =  $moduleDir.'info.ini';
            if (!is_file($infoFile)) {
                throw new Exception('升级文件完整！');
            }

            $info = parse_ini_file($infoFile, true, INI_SCANNER_TYPED) ?: [];
            if ($info['version'] == config('badouadmin.version')) {
                throw new Exception('当前版本已是最新版本！');
            }

            // 备份冲突文件
            $conflictFiles = $this->getGlobalFiles($moduleDir, true);
            if ($conflictFiles) {
                if (!$force) {
                    throw new ModuleException('存在冲突文件，请先备份！', 0, ['conflictFiles' => $conflictFiles]);
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
        } catch (ModuleException $e) {
            $this->result(__($e->getMessage()), $e->getData(), 0, -1);
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Update successful'));
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
}

<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use ba\Version;
use bd\service\upgrade\Upgrade as UpgradeService;

class Upgrade extends Backend
{
    /**
     * 获取最新版本
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ba\Exception
     */
    public function version()
    {
        $content = UpgradeService::version('badoucms');
        if (!$content) {
            $this->error('获取最新版本失败');
        }
        $oldVersion = config('badoucms.version');
        $newVersion = $content['version'];
        /* 线上版本>本地版本时会返回false 表示有最新版本*/
        $isUpdate = Version::compare($newVersion, $oldVersion);
        if (!$isUpdate) {
            $this->success('有新版本', ['version' => $newVersion]);
        }
        $this->error('无新版本', ['version' => $oldVersion]);
    }

    /**
     * 获取可升级列表
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ba\Exception
     */
    public function getList(): void
    {
        $oldVersion = config('badoucms.version');
        $content = UpgradeService::getList('badoucms', $oldVersion);
        if (!$content) {
            $this->success('获取版本列表成功', []);
        }
        $this->success('获取版本列表成功', $content);
    }

    /**
     * 备份
     * @return void
     */
    public function backup(): void
    {
        try {
            $oldVersion = config('badoucms.version');
            UpgradeService::backup('badoucms', $oldVersion);
            //备份数据库
            $backupdb = new \bd\Backupdb();
            $backupdb->backupDb();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        $this->success('备份成功');
    }

    /**
     * 升级
     * @return void
     */
    public function update(): void
    {
        try {
            $version = $this->request->param('version');
            //下载文件
            $zipPath = UpgradeService::download('badoucms', $version);
            $ignoreDirs = ['config'];
            $ignoreFiles = [];
            //解压文件
            UpgradeService::unzip($zipPath, root_path(), $ignoreDirs, $ignoreFiles);
            //执行sql
            UpgradeService::sql();
            // 删除下载的zip
            @unlink($zipPath);

            //调用升级后的方法
            UpgradeService::upgradeAfter('badoucms', $version);
            //触发升级后事件
            event('upgrade_after', ['name' => 'badoucms', 'version' => $version]);

        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        $this->success('ok');
    }
}

<?php

namespace app\admin\controller;

use app\common\controller\Backend;

class Backupdb extends Backend
{
    protected array $noNeedPermission = [
        'backupTable',
        'optimize',
        'repair',
    ];

    public function index(): void
    {
        $backupdb = new \bd\Backupdb();
        $list = $backupdb->getTables();
        $this->success('', [
            'list'   => $list,
            'remark' => '',
            'total'  => count($list),
        ]);
    }

    // 备份表
    public function backupTable()
    {
        $backupdb = new \bd\Backupdb();
        $list = $this->getTableList();
        $backupdb->backupTable($list);
        $this->success('备份成功');
    }

    // 备份数据库
    public function backupDb()
    {
        $backupdb = new \bd\Backupdb();
        $backupdb->backupDb();
        $this->success('备份成功');
    }

    // 优化表
    public function optimize()
    {
        $backupdb = new \bd\Backupdb();
        $list = $this->getTableList();
        if (! $list) {
            $this->error('请选择数据表！');
        }
        $backupdb->optimize(implode(',', $list));
        $this->success('优化成功');
    }

    // 修复表
    public function repair()
    {
        $backupdb = new \bd\Backupdb();
        $tables = $this->getTableList();
        if (! $tables) {
            $this->error('请选择数据表！');
        }
        if ($backupdb->repair(implode(',', $tables))) {
            $this->success('修复成功');
        } else {
            $this->error('修复失败');
        }
    }

    // 获取并检查表名称
    protected function getTableList()
    {
        $list = $this->request->param('tables/a');
        foreach ($list as $key => $value) {
            if (! preg_match('/^[\w]+$/', $value)) {
                unset($list[$key]);
            }
        }
        return $list;
    }
}

<?php

namespace app\admin\controller;

use app\admin\command\Crud;
use app\common\controller\Backend;
use app\common\service\TableDesignService;
use badou\TableManager;
use think\console\Input;
use think\console\Output;
use think\facade\Db;

/**
 * 数据表设计：创建表和字段后调用 CRUD 生成后台功能
 */
class Tabledesign extends Backend
{
    protected $noNeedRight = [];

    public function initialize()
    {
        parent::initialize();
        if (!$this->auth->isSuperAdmin()) {
            $this->error(__('Access is allowed only to the super management group'));
        }
    }

    /**
     * 数据表列表
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $config = TableManager::getConnectionConfig();
            $prefix = (string)$config['prefix'];
            $rows = Db::query(
                'SELECT TABLE_NAME, ENGINE, TABLE_COLLATION, TABLE_COMMENT, CREATE_TIME, UPDATE_TIME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
                [$config['database']]
            );
            $list = [];
            foreach ($rows as $row) {
                $full = $row['TABLE_NAME'];
                $bare = $prefix !== '' && stripos($full, $prefix) === 0 ? substr($full, strlen($prefix)) : $full;
                $list[] = [
                    'name' => $bare,
                    'full_name' => $full,
                    'engine' => $row['ENGINE'],
                    'comment' => $row['TABLE_COMMENT'],
                    'collation' => $row['TABLE_COLLATION'],
                    'create_time' => $row['CREATE_TIME'],
                    'update_time' => $row['UPDATE_TIME'],
                ];
            }
            $this->result('ok', $list, count($list));
        }
        return $this->view->fetch();
    }

    /**
     * 新建数据表
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $row = $this->request->post('row/a', []);
            $fields = $this->request->post('fields/a', []);
            $service = new TableDesignService();
            try {
                $service->createTable(
                    (string)($row['name'] ?? ''),
                    trim((string)($row['comment'] ?? '')),
                    (string)($row['engine'] ?? 'InnoDB'),
                    $fields
                );
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
            }
            $this->success('数据表创建成功');
        }
        $this->view->assign('typeList', (new TableDesignService())->typeOptions());
        return $this->view->fetch();
    }

    /**
     * 修改表注释与引擎
     */
    public function edit()
    {
        $name = (string)$this->request->param('name');
        $service = new TableDesignService();
        try {
            $bare = $service->bareName($name);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        $info = $this->tableInfo($bare);
        if (!$info) {
            $this->error('数据表不存在');
        }
        if ($this->request->isPost()) {
            $row = $this->request->post('row/a', []);
            try {
                $service->updateTable($bare, trim((string)($row['comment'] ?? '')), (string)($row['engine'] ?? 'InnoDB'));
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
            }
            $this->success('修改成功');
        }
        $this->view->assign('row', $info);
        return $this->view->fetch();
    }

    /**
     * 删除数据表
     */
    public function del()
    {
        $name = (string)$this->request->param('name');
        $service = new TableDesignService();
        try {
            $service->dropTable($name);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        $this->success('数据表已删除');
    }

    /**
     * 字段列表
     */
    public function fields()
    {
        $name = (string)$this->request->param('table');
        $service = new TableDesignService();
        try {
            $bare = $service->bareName($name);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        if ($this->request->isAjax()) {
            $columns = TableManager::getTableColumns($bare);
            $list = [];
            foreach ($columns as $column) {
                $list[] = [
                    'name' => $column['COLUMN_NAME'],
                    'type' => $column['COLUMN_TYPE'],
                    'nullable' => $column['IS_NULLABLE'],
                    'key' => $column['COLUMN_KEY'],
                    'default' => $column['COLUMN_DEFAULT'],
                    'extra' => $column['EXTRA'],
                    'comment' => $column['COLUMN_COMMENT'],
                ];
            }
            $this->result('ok', $list, count($list));
        }
        $this->view->assign('table', $bare);
        return $this->view->fetch();
    }

    /**
     * 添加字段
     */
    public function fieldadd()
    {
        $name = (string)$this->request->param('table');
        $service = new TableDesignService();
        try {
            $bare = $service->bareName($name);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        if ($this->request->isPost()) {
            $field = $this->request->post('field/a', []);
            try {
                $service->addField($bare, $field);
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
            }
            $this->success('字段添加成功');
        }
        $this->view->assign('table', $bare);
        $this->view->assign('typeList', $service->typeOptions());
        return $this->view->fetch('field_form');
    }

    /**
     * 修改字段
     */
    public function fieldedit()
    {
        $name = (string)$this->request->param('table');
        $field = (string)($this->request->isPost() ? $this->request->post('origin') : $this->request->get('field'));
        $service = new TableDesignService();
        try {
            $bare = $service->bareName($name);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        try {
            $origin = $service->fieldName($field);
        } catch (\Throwable $e) {
            $this->error('字段名' . mb_substr($e->getMessage(), 3));
        }
        $columns = TableManager::getTableColumns($bare);
        if (!isset($columns[$origin])) {
            $this->error('字段不存在');
        }
        if ($this->request->isPost()) {
            $data = $this->request->post('field/a', []);
            try {
                $service->changeField($bare, $origin, $data);
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
            }
            $this->success('字段修改成功');
        }
        $this->view->assign('table', $bare);
        $this->view->assign('origin', $origin);
        $this->view->assign('column', $columns[$origin]);
        $this->view->assign('typeList', $service->typeOptions());
        return $this->view->fetch('field_form');
    }

    /**
     * 删除字段
     */
    public function fielddel()
    {
        $name = (string)$this->request->param('table');
        $field = (string)$this->request->get('field');
        $service = new TableDesignService();
        try {
            $service->dropField($name, $field);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        $this->success('字段已删除');
    }

    /**
     * 调用 CRUD 命令生成控制器、模型、视图和菜单
     */
    public function build()
    {
        $name = (string)$this->request->param('name');
        $service = new TableDesignService();
        try {
            $bare = $service->bareName($name);
            $service->assertDesignable($bare);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        if (!$service->exists($bare)) {
            $this->error('数据表不存在');
        }
        if ($this->request->isPost()) {
            $row = $this->request->post('row/a', []);
            $controller = trim((string)($row['controller'] ?? ''));
            $menu = trim((string)($row['menu'] ?? ''));
            $force = !empty($row['force']) ? 'true' : 'false';
            $argv = ['--table=' . $bare, '--force=' . $force, '--menu=' . ($menu !== '' ? $menu : '1')];
            if ($controller !== '') {
                $argv[] = '--controller=' . $controller;
            }
            try {
                $output = new Output('buffer');
                $command = $this->app->invokeClass(Crud::class);
                $command->run(new Input($argv), $output);
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
            }
            $this->success('生成成功，请刷新后台查看新菜单');
        }
        $info = $this->tableInfo($bare);
        $this->view->assign('table', $bare);
        $this->view->assign('comment', $info['comment'] ?? $bare);
        return $this->view->fetch();
    }

    protected function tableInfo(string $bare): array
    {
        $config = TableManager::getConnectionConfig();
        $full = TableManager::tableName($bare, true);
        $rows = Db::query(
            'SELECT ENGINE, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$config['database'], $full]
        );
        if (!$rows) {
            return [];
        }
        return [
            'name' => $bare,
            'engine' => $rows[0]['ENGINE'],
            'comment' => $rows[0]['TABLE_COMMENT'],
        ];
    }
}

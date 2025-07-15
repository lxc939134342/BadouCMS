<?php

namespace app\admin\controller;

use ZipArchive;
use think\Exception;
use think\facade\Db;
use badou\TableManager;
use think\facade\Config;
use app\common\controller\Backend;
use badou\Filesystem;
use think\db\exception\PDOException;

class Database extends Backend
{
    protected $database = '';

    protected $prefix = '';

    protected $noNeedRight = ['backuplist'];

    protected $backupDir = '';

    public function initialize()
    {
        parent::initialize();
        if (!$this->auth->isSuperAdmin()) {
            $this->error(__('Access is allowed only to the super management group'));
        }
        $database = config('database');
        $this->database = $database['connections']['mysql']['database'];
        $this->prefix = $database['connections']['mysql']['prefix'];
        $this->backupDir = root_path().'runtime'.DIRECTORY_SEPARATOR. 'database' . DIRECTORY_SEPARATOR;
    }

    public function index()
    {
        $page = $this->request->get("page", 1);
        $limit = $this->request->get("limit");
        $offset = ($page - 1) * $limit;
        if ($this->request->isAjax()) {
            $group = $group ?? 'system';
            $tables = $this->getTables($group);

            $list = [];
            if (count($tables) > 0) {
                $tableInfos = [];
                foreach ($tables as $k => $v) {
                    $tableInfos[] = Db::table("information_schema.TABLES")->field("*")->where(['TABLE_SCHEMA' => $this->database, 'TABLE_NAME' => $v])->find();
                }
                $i = 1;
                foreach ($tableInfos as $key => $tableInfo) {
                    $list[$key]['id'] = $i++;
                    $list[$key]['group'] = $group;
                    // $list[$key]['is_admin'] = ($group == 'system' && !$config['is_admin']) ? 0 : 1;
                    $list[$key]['is_has'] = $this->prefix !== '' ? 1 : 0;
                    $list[$key]['name'] = $tableInfo['TABLE_NAME'];
                    $list[$key]['engine'] = $tableInfo['ENGINE'];
                    $list[$key]['rows'] = Db::table($tableInfo['TABLE_NAME'])->count();
                    $list[$key]['field_nums'] = count(Db::getFields($tableInfo['TABLE_NAME']));
                    $list[$key]['charset'] = substr($tableInfo['TABLE_COLLATION'], 0, strpos($tableInfo['TABLE_COLLATION'], '_'));
                    $list[$key]['collation'] = $tableInfo['TABLE_COLLATION'];
                    $list[$key]['comment'] = $tableInfo['TABLE_COMMENT'];
                    $list[$key]['create_time'] = $tableInfo['CREATE_TIME'];
                    $list[$key]['update_time'] = $tableInfo['UPDATE_TIME'];
                }
            }

            $this->result('ok', array_slice($list, $offset, $limit), count($list));
        }
        return $this->view->fetch();
    }

    /**
     * 获取数据库表
     */
    protected function getTables($group = 'all')
    {
        $tables = Db::getTables();

        //数据表分组
        $result = [];
        $result['system'] = [];
        foreach ($tables as $index => $table) {
            $result['system'][] = $table;
        }
        $result['system'] = array_values($tables);
        return $group === 'all' ? $result : (isset($result[$group]) ? $result[$group] : []);
    }

    /**
     * 优化表
     */
    public function optimize()
    {
        Db::startTrans();
        try {
            $list = Db::query("SHOW TABLES");
            foreach ($list as $key => $row) {
                $name = reset($row);
                Db::execute("OPTIMIZE TABLE {$name}");
            }
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success();
    }

    /**
     * 修复表
     */
    public function repair()
    {
        Db::startTrans();
        try {
            $list = Db::query("SHOW TABLES");
            foreach ($list as $key => $row) {
                $name = reset($row);
                Db::execute("REPAIR TABLE {$name}");
            }
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success();
    }

    /**
     * 备份列表
     */
    public function backuplist()
    {
        $group = $this->request->get("group");
        $offset = $this->request->get("offset");
        $limit = $this->request->get("limit");
        if ($this->request->isAjax()) {
            $filter = $this->request->request("filter", '', 'trim');
            $filter = (array) json_decode($filter, true);
            $module = !isset($filter['module']) ? 'all' : $filter['module'];
            $type = !isset($filter['type']) ? 'all' : $filter['type'];

            $backuplist = [];
            $files = [];
            foreach (glob($this->backupDir . "*.*") as $key => $filename) {
                $basename = basename($filename);
                $file_arr = stripos($basename, '-') !== false ? explode('-', $basename) : $basename;
                $_module = (is_array($file_arr) && $file_arr[0] == 'backup') ? $file_arr[2] : 'all';
                $_type = (is_array($file_arr) && $file_arr[0] == 'backup') ? $file_arr[3] : 'all';
                $time = filemtime($filename);

                if (!in_array($basename, $files)) {
                    $backuplist[$time] =
                        [
                            'file' => $basename,
                            'module' => $_module,
                            'module_name' => '全部',
                            'type' => $_type,
                            'date' => date("Y-m-d H:i:s", $time),
                            'size' => format_bytes(filesize($filename))
                        ];
                    array_push($files, $basename);
                    if ($module !== 'all' && $module !== $_module) {
                        unset($backuplist[$time]);
                    } elseif ($type !== 'all' && $type !== $_type) {
                        unset($backuplist[$time]);
                    }
                }
            }
            krsort($backuplist);
            $this->result('ok', array_slice($backuplist, $offset, $limit), count($backuplist));
        }
        return $this->view->fetch();
    }

    /**
     * 恢复
     */
    public function restore()
    {
        if ($this->request->isPost()) {
            $file = $this->request->request('file');
            if (!preg_match("/\.(zip|sql?)$/", $file)) {
                $this->error(__("Invalid parameters"));
            }
            $file = $this->backupDir . $file;
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (!class_exists('ZipArchive')) {
                $this->error(__("Zip tips 1"));
            }
            try {
                if ($ext == 'zip') {
                    if (!is_dir($this->backupDir)) {
                        @mkdir($this->backupDir, 0755);
                    }

                    $zip = new ZipArchive();
                    if ($zip->open($file) !== true) {
                        throw new Exception(__('Can not open zip file'));
                    }
                    if (!$zip->extractTo($this->backupDir)) {
                        $zip->close();
                        throw new Exception(__('Can not unzip file'));
                    }
                    $zip->close();
                    $filename = basename($file);
                    $sqlFile = $this->backupDir . str_replace('.zip', '.sql', $filename);
                } else {
                    $sqlFile = $file;
                }

                if (!is_file($sqlFile)) {
                    throw new Exception(__('Sql file not found'));
                }
                $lines = file($sqlFile);
                $templine = '';
                foreach ($lines as $line) {
                    if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*') {
                        continue;
                    }

                    $templine .= $line;
                    if (substr(trim($line), -1, 1) == ';') {
                        $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                        try {
                            Db::getPdo()->exec($templine);
                        } catch (\PDOException $e) {
                            p($e->getMessage());
                        }
                        $templine = '';
                    }
                }
            } catch (Exception $e) {
                $this->error($e->getMessage());
            } catch (PDOException $e) {
                $this->error($e->getMessage());
            }
            $this->success(__('Restore successful'));
        }
    }

    /**
     * 备份
     */
    public function backup()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            Filesystem::mkdir($this->backupDir);
            $tableList = [];
            $list = Db::query("SHOW TABLES");
            foreach ($list as $key => $row) {
                if ($params['module'] == 'all') {
                    $tableList[] = reset($row);
                } else {
                    $tmp = explode('_', reset($row));
                    if ($this->prefix !== '' && $tmp[1] == $params['module']) {
                        $tableList[] = reset($row);
                    } elseif ($this->prefix == '' && $tmp[0] == $params['module']) {
                        $tableList[] = reset($row);
                    }
                }
            }
            if (!class_exists('ZipArchive')) {
                $this->error(__("Zip tips 2"));
            }
            try {
                TableManager::backup('all', 1, $this->backupDir);
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success();
        }
        return $this->view->fetch();
    }

    /* 删除备份 */
    public function backupdel()
    {
        $file = $this->request->request('file');
        if (!preg_match("/\.(zip|sql?)$/", $file)) {
            $this->error(__("Invalid parameters"));
        }
        $file = $this->backupDir . $file;
        unlink($file);
        $this->success(__('Delete successful'));
    }
}

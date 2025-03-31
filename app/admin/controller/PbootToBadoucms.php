<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\facade\Db;

class PbootToBadoucms extends Backend
{
    // 表名映射关系
    protected $tableMap = [
        'ay_area' => 'bd_cms_area',
        'ay_company' => 'bd_cms_company',
        'ay_content' => 'bd_cms_content',
        'ay_content_ext' => 'bd_cms_content_ext',
        'ay_content_sort' => 'bd_cms_content_sort',
        'ay_extfield' => 'bd_cms_extfield',
        'ay_form' => 'bd_cms_form',
        'ay_form_field' => 'bd_cms_form_field',
        'ay_label' => 'bd_cms_label',
        'ay_link' => 'bd_cms_link',
        'ay_member_comment' => 'bd_cms_member_comment',
        'ay_member_field' => 'bd_cms_member_field',
        'ay_member_group' => 'bd_cms_member_group',
        'ay_message' => 'bd_cms_message',
        'ay_model' => 'bd_cms_model',
        'ay_slide' => 'bd_cms_slide',
        'ay_site' => 'bd_cms_site',
        'ay_tags' => 'bd_cms_tags',
    ];

    // 字段映射关系(key为pboot字段,value为badoucms字段)
    protected $fieldMap = [];

    protected array $noNeedPermission = ['*'];

    public function index(): void
    {
        $this->success('PbootCMS数据迁移');
    }

    /**
     * 获取PbootCMS路径
     */
    protected function getPbootPath()
    {
        $pbootPath = $this->request->post('pbootPath') ;
        if (!$pbootPath) {
            $this->error('请先传入PbootCMS文件夹');
        }
        $pbootPath = root_path().$pbootPath; // 固定使用项目根目录
        if (!is_dir($pbootPath)) {
            $this->error('请将pbootcms程序复制到项目根目录下');
        }
        return $pbootPath;
    }

    /**
     * 获取待迁移的表列表
     */
    public function getTables()
    {
        $pbootPath = $this->getPbootPath();
        try {
            $dbType = $this->setupPbootConnection($pbootPath);
            $tables = [];

            // 获取系统表
            foreach ($this->tableMap as $sourceTable => $targetTable) {
                $count = 0;
                try {
                    $count = Db::connect('pboot')->table($sourceTable)->count();
                } catch (\Exception $e) {
                    continue;
                }
                $tables[] = [
                    'name' => $sourceTable,
                    'target' => $targetTable,
                    'type' => 'system',
                    'count' => $count
                ];
            }

            // 获取自定义表
            if ($dbType == 'sqlite') {
                $customTables = Db::connect('pboot')->query("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'ay_diy_%'");
                $customTables = array_map(function ($item) { return ['name' => $item['name']]; }, $customTables);
            } else {
                $customTables = Db::connect('pboot')->query("SHOW TABLES LIKE 'ay_diy_%'");
                $customTables = array_map(function ($item) { return ['name' => current($item)]; }, $customTables);
            }

            foreach ($customTables as $table) {
                $sourceName = $table['name'];
                $targetName = str_replace('ay_diy_', 'bd_cms_diy_', $sourceName);
                $count = Db::connect('pboot')->table($sourceName)->count();
                $tables[] = [
                    'name' => $sourceName,
                    'target' => $targetName,
                    'type' => 'custom',
                    'count' => $count
                ];
            }

            return json(['code' => 1, 'msg' => '获取成功', 'data' => $tables]);
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '获取表列表失败：' . $e->getMessage()]);
        }
    }

    /**
     * 验证PbootCMS配置
     */
    protected function setupPbootConnection($pbootPath)
    {
        // 检查配置文件
        $configFile = $pbootPath . '/config/database.php';
        if (!file_exists($configFile)) {
            throw new \Exception('数据库配置文件不存在');
        }

        // 读取配置文件
        $config = include $configFile;
        $dbConfig = $config['database'] ?? [];
        $dbType = $dbConfig['type'] ?? '';

        // 将pdo_mysql映射为mysql
        if ($dbType == 'pdo_mysql' || $dbType == 'mysqli') {
            $dbType = 'mysql';
        }
        $dbdatabs = config('database');
        $dbconnections = $dbdatabs['connections'] ?? [];
        if ($dbType == 'sqlite' || $dbType == 'pdo_sqlite') {
            $dbPath = $dbConfig['dbname'] ?? '';
            if (empty($dbPath)) {
                throw new \Exception('SQLite数据库文件路径未配置');
            }
            $dbPath = $pbootPath . $dbPath;
            if (!file_exists($dbPath)) {
                throw new \Exception('SQLite数据库文件不存在');
            }
            $dbconnections['pboot'] = [
                'type' => 'sqlite',
                'database' => $dbPath,
                'prefix' => 'ay_'
            ];
            // 配置SQLite连接

        } elseif ($dbType == 'mysql') {
            $dbconnections['pboot'] = [
                'type' => 'mysql',
                'hostname' => $dbConfig['host'] ?? '',
                'database' => $dbConfig['dbname'] ?? '',
                'username' => $dbConfig['user'] ?? '',
                'password' => $dbConfig['passwd'] ?? '',
                'hostport' => $dbConfig['port'] ?? '3306',
                'charset' => 'utf8',
                'prefix' => 'ay_',
                'params' => [
                    \PDO::ATTR_PERSISTENT => true,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET wait_timeout=28800, interactive_timeout=28800'
                ]
            ];
            // 配置MySQL连接
        } else {
            throw new \Exception('不支持的数据库类型');
        }
        $dbdatabs['connections'] = $dbconnections;
        config(['database' => $dbdatabs]);

        // 测试数据库连接
        Db::connect('pboot')->query('SELECT 1');

        return $dbType;
    }

    public function checkConfig()
    {
        $pbootPath = $this->getPbootPath();// 获取项目根目录
        try {
            $dbType = $this->setupPbootConnection($pbootPath);
            return json(['code' => 1, 'msg' => '配置验证成功', 'data' => ['type' => $dbType]]);
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '数据库连接失败：' . $e->getMessage()]);
        }
    }

    /**
     * 执行数据迁移
     */
    public function migrate()
    {
        $pbootPath = $this->getPbootPath();
        try {
            // 获取PbootCMS路径和表名
            $params = $this->request->post();
            $tableName = $params['tableName'] ?? '';
            // 设置PbootCMS数据库连接
            $dbType = $this->setupPbootConnection($pbootPath);

            // 获取所有自定义表
            if ($dbType == 'sqlite') {
                $tables = Db::connect('pboot')->query("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'ay_diy_%'");
                $tables = array_map(function ($item) { return ['name' => $item['name']]; }, $tables);
            } else {
                $tables = Db::connect('pboot')->query('SHOW TABLES');
            }

            foreach ($tables as $table) {
                $customTableName = $dbType == 'sqlite' ? $table['name'] : current($table);
                if (strpos($customTableName, 'ay_diy_') === 0) {
                    $targetTable = str_replace('ay_diy_', 'bd_cms_diy_', $customTableName);
                    $this->tableMap[$customTableName] = $targetTable;

                    // 检查目标表是否存在，如果存在则先删除
                    try {
                        Db::execute("DROP TABLE IF EXISTS {$targetTable}");
                    } catch (\Exception $e) {
                        throw new \Exception('删除旧表失败：' . $e->getMessage());
                    }

                    // 创建新表
                    if ($dbType == 'sqlite') {
                        $createTableSql = Db::connect('pboot')->query("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$customTableName])[0]['sql'];
                        // 转换SQLite的建表语句为MySQL格式
                        $createTableSql = preg_replace('/INTEGER\s+PRIMARY\s+KEY\s+AUTOINCREMENT/i', 'INT AUTO_INCREMENT PRIMARY KEY', $createTableSql);
                        $createTableSql = preg_replace('/INTEGER(?!\s+PRIMARY\s+KEY)/i', 'INT', $createTableSql);
                        $createTableSql = preg_replace('/DATETIME\s+DEFAULT\s+CURRENT_TIMESTAMP/i', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $createTableSql);

                        $createTableSql = preg_replace_callback('/VARCHAR\((\d+)\)/i', function ($matches) {
                            $length = intval($matches[1]);
                            return $length <= 255 ? "VARCHAR($length)" : "TEXT";  // 保留<=255的VARCHAR定义
                        }, $createTableSql);
                        $createTableSql = str_replace('"', '`', $createTableSql);
                        $createTableSql = str_replace($customTableName, $targetTable, $createTableSql);
                        // 移除SQLite特有的语法
                        $createTableSql = preg_replace('/\s*AUTOINCREMENT\s*/i', ' AUTO_INCREMENT ', $createTableSql);
                    } else {
                        $createTableSql = Db::connect('pboot')->query("SHOW CREATE TABLE {$customTableName}")[0]['Create Table'];
                        $createTableSql = str_replace($customTableName, $targetTable, $createTableSql);
                    }
                    try {
                        Db::execute($createTableSql);
                    } catch (\Exception $e) {
                        throw new \Exception('创建表失败：' . $e->getMessage() . '\nSQL: ' . $createTableSql);
                    }
                }
            }

            // 关闭外键约束
            Db::execute('SET FOREIGN_KEY_CHECKS = 0');

            // 如果指定了表名，则只迁移该表
            if (!empty($tableName)) {
                // 检查是否是系统表
                $isSystemTable = isset($this->tableMap[$tableName]);
                // 检查是否是自定义表
                $isCustomTable = strpos($tableName, 'ay_diy_') === 0;

                if (!$isSystemTable && !$isCustomTable) {
                    return json(['code' => 0, 'msg' => '指定的表不存在或不是有效的PbootCMS表']);
                }

                // 如果是自定义表且未在映射中，则添加到映射
                if ($isCustomTable && !isset($this->tableMap[$tableName])) {
                    $targetTable = str_replace('ay_diy_', 'bd_cms_diy_', $tableName);
                    $this->tableMap[$tableName] = $targetTable;

                    // 检查目标表是否存在，如果存在则先删除
                    try {
                        Db::execute("DROP TABLE IF EXISTS {$targetTable}");
                    } catch (\Exception $e) {
                        return json(['code' => 0, 'msg' => '删除旧表失败：' . $e->getMessage()]);
                    }

                    // 创建新表
                    if ($dbType == 'sqlite') {
                        $createTableSql = Db::connect('pboot')->query("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$tableName])[0]['sql'];
                        p($createTableSql);
                        halt(111);
                        // 转换SQLite的建表语句为MySQL格式
                        $createTableSql = preg_replace('/INTEGER\s+PRIMARY\s+KEY\s+AUTOINCREMENT/i', 'INT AUTO_INCREMENT PRIMARY KEY', $createTableSql);
                        $createTableSql = preg_replace('/INTEGER(?!\s+PRIMARY\s+KEY)/i', 'INT', $createTableSql);
                        $createTableSql = preg_replace('/DATETIME\s+DEFAULT\s+CURRENT_TIMESTAMP/i', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $createTableSql);

                        // 修改所有可能存储长文本的字段为 LONGTEXT
                        $createTableSql = preg_replace('/VARCHAR\s*\(\d+\)\s*(?=,|\))/i', 'LONGTEXT', $createTableSql);
                        $createTableSql = preg_replace('/TEXT\s*\(\d+\)\s*(?=,|\))/i', 'LONGTEXT', $createTableSql);
                        $createTableSql = preg_replace('/TEXT(?!\()/i', 'LONGTEXT', $createTableSql);

                        $createTableSql = str_replace('"', '`', $createTableSql);
                        $createTableSql = str_replace($tableName, $targetTable, $createTableSql);
                        $createTableSql = preg_replace('/\s*AUTOINCREMENT\s*/i', ' AUTO_INCREMENT ', $createTableSql);
                    } else {
                        $createTableSql = Db::connect('pboot')->query("SHOW CREATE TABLE {$tableName}")[0]['Create Table'];
                        $createTableSql = str_replace($tableName, $targetTable, $createTableSql);
                    }

                    try {
                        Db::execute($createTableSql);
                    } catch (\Exception $e) {
                        return json(['code' => 0, 'msg' => '创建表失败：' . $e->getMessage()]);
                    }
                }

                $targetTable = $this->tableMap[$tableName];
                $this->migrateTable($tableName, $targetTable);

                return json(['code' => 1, 'msg' => '表 ' . $tableName . ' 迁移完成']);
            }

            return json(['code' => 0, 'msg' => '请指定要迁移的表名']);
        } catch (\Exception $e) {

            return json(['code' => 0, 'msg' => '数据迁移失败: ' . $e->getMessage()]);
        }
    }

    /**
     * 迁移单个表的数据
     */
    protected function migrateTable($sourceTable, $targetTable)
    {
        // 清空目标表
        Db::execute("TRUNCATE TABLE {$targetTable}");

        // 设置每批处理的数量
        $batchSize = 100;
        $offset = 0;

        while (true) {
            try {
                // 分批获取源表数据
                $data = Db::connect('pboot')
                    ->table($sourceTable)
                    ->limit($offset, $batchSize)
                    ->select()
                    ->toArray();
            } catch (\Exception $e) {
                throw new \Exception('数据获取失败：' . $e->getMessage());
            }

            if (empty($data)) {
                break;
            }

            // 转换数据
            $transformedData = $this->transformData($data, $sourceTable);
            // 批量插入数据
            Db::table($targetTable)->insertAll($transformedData);

            $offset += $batchSize;

            // 释放内存
            unset($data, $transformedData);
            gc_collect_cycles();

        }
    }

    /**
     * 迁移文件
     * @return void
     */
    public function migrateFiles()
    {
        $pbootPath = $this->getPbootPath();
        try {
            $sourcePath = $pbootPath. DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR.'upload';
            $targetPath = public_path(). '/upload';
            $this->copyDirectory($sourcePath, $targetPath);
        } catch (\Exception $e) {
            return json(['code' => 0,'msg' => '文件迁移失败: '. $e->getMessage()]);
        }
        $this->success('文件迁移成功');
    }

    /**
     * 复制目录及其内容
     */
    protected function copyDirectory($source, $target)
    {
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $sourceFile = $source . '/' . $file;
                $targetFile = $target . '/' . $file;

                if (is_dir($sourceFile)) {
                    $this->copyDirectory($sourceFile, $targetFile);
                } else {
                    copy($sourceFile, $targetFile);
                }
            }
        }
        closedir($dir);
    }

    /**
     * 转换数据
     */
    protected function transformData($data, $sourceTable)
    {
        $transformedData = [];
        $targetTable = $this->tableMap[$sourceTable];

        // 获取目标表的字段信息
        $columns = Db::getFields($targetTable);


        $validFields = array_keys($columns);

        foreach ($data as $row) {
            $newRow = [];
            foreach ($row as $field => $value) {
                // 如果有字段映射关系，则进行转换
                if (isset($this->fieldMap[$sourceTable][$field])) {
                    $targetField = $this->fieldMap[$sourceTable][$field];
                } else {
                    $targetField = $field;
                }

                // 只保留目标表中存在的字段
                if (in_array($targetField, $validFields)) {
                    // 检查字段类型和数据长度
                    if (isset($columns[$targetField])) {
                        $type = strtoupper($columns[$targetField]['type']);

                        // 处理可能超长的字段
                        if (is_string($value)) {
                            if (preg_match('/VARCHAR\((\d+)\)/i', $type, $matches)) {
                                $length = intval($matches[1]);
                                // 如果数据长度超过字段限制，自动转换字段类型为TEXT
                                if (mb_strlen($value) > floor($length / 4)) {
                                    try {
                                        Db::execute("ALTER TABLE {$targetTable} MODIFY COLUMN `{$targetField}` TEXT");
                                        $columns[$targetField]['type'] = 'TEXT';
                                        unset($columns[$targetField]['length']);
                                    } catch (\Exception $e) {
                                        // 如果无法修改字段类型，则截断数据
                                        $value = mb_substr($value, 0, floor($length / 4));
                                    }
                                }
                            }
                        }
                    }
                    $newRow[$targetField] = $value;
                }
            }
            if (!empty($newRow)) {
                $transformedData[] = $newRow;
            }
        }

        return $transformedData;
    }
}

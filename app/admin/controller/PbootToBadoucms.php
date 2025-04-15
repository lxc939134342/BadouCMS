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
                    // 修改查询方式，使用更通用的方法
                    $exists = Db::connect('pboot')->query("SELECT 1 FROM {$sourceTable} LIMIT 1");
                    if ($exists !== false) {
                        $count = Db::connect('pboot')->table($sourceTable)->count();
                    }
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
            $customTables = [];
            // 获取自定义表
            if (stripos($dbType, 'sqlite') !== false) {
                // 使用 SQLite 特定的查询语法
                $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'ay_diy_%' ORDER BY name";
                $customTables = Db::connect('pboot')->query($sql);
            } else {
                // MySQL 语法
                $customTables = Db::connect('pboot')->query("SHOW TABLES LIKE 'ay_diy_%'");
                $customTables = array_map(function ($item) {
                    return ['name' => current($item)];
                }, $customTables);
            }

            foreach ($customTables as $table) {
                $sourceName = $table['name'];
                $targetName = str_replace('ay_diy_', 'bd_cms_diy_', $sourceName);
                try {
                    $count = Db::connect('pboot')->table($sourceName)->count();
                } catch (\Exception $e) {
                    $count = 0;
                }
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
            if (stripos($dbType, 'sqlite') !== false) {
                $tables = Db::connect('pboot')->query("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'ay_diy_%'");
                $tables = array_map(function ($item) { return ['name' => $item['name']]; }, $tables);
            } else {
                $tables = Db::connect('pboot')->query('SHOW TABLES');
            }

            foreach ($tables as $table) {
                $customTableName = stripos($dbType, 'sqlite') !== false ? $table['name'] : current($table);
                if (strpos($customTableName, 'ay_diy_') === 0) {
                    $targetTable = str_replace('ay_diy_', 'bd_cms_diy_', $customTableName);
                    $this->tableMap[$customTableName] = $targetTable;
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

    protected function createTable($sourceTable, $targetTable, $dbType)
    {
        // 检查目标表是否存在，如果存在则先删除
        try {
            Db::execute("DROP TABLE IF EXISTS {$targetTable}");
        } catch (\Exception $e) {
            throw new \Exception('删除旧表失败：' . $e->getMessage());
        }

        // 创建新表
        if (stripos($dbType, 'sqlite') !== false) {
            $createTableSql = Db::connect('pboot')->query("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$sourceTable])[0]['sql'];
            // 转换SQLite的建表语句为MySQL格式
            $createTableSql = preg_replace('/INTEGER\s+PRIMARY\s+KEY\s+AUTOINCREMENT/i', 'BIGINT AUTO_INCREMENT PRIMARY KEY', $createTableSql);
            $createTableSql = preg_replace('/INTEGER(?!\s+PRIMARY\s+KEY)/i', 'BIGINT', $createTableSql);
            $createTableSql = preg_replace('/DATETIME\s+DEFAULT\s+CURRENT_TIMESTAMP/i', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $createTableSql);

            // 处理TEXT字段
            $createTableSql = preg_replace_callback('/TEXT\((\d+)\)/i', function ($matches) {
                $length = intval($matches[1]);
                return $length <= 255 ? "VARCHAR($length)" : "TEXT";
            }, $createTableSql);

            // 移除TEXT类型的默认值
            $createTableSql = preg_replace('/TEXT\s+NOT\s+NULL\s+DEFAULT\s+\'[^\']*\'/i', 'TEXT NOT NULL', $createTableSql);

            $createTableSql = str_replace('"', '`', $createTableSql);
            $createTableSql = str_replace($sourceTable, $targetTable, $createTableSql);
            // 移除SQLite特有的语法
            $createTableSql = preg_replace('/\s*AUTOINCREMENT\s*/i', ' AUTO_INCREMENT ', $createTableSql);
        } else {
            $createTableSql = Db::connect('pboot')->query("SHOW CREATE TABLE {$sourceTable}")[0]['Create Table'];
            $createTableSql = str_replace($sourceTable, $targetTable, $createTableSql);
        }
        try {
            Db::execute($createTableSql);
        } catch (\Exception $e) {
            throw new \Exception('创建表失败：' . $e->getMessage() . '\nSQL: ' . $createTableSql);
        }
    }

    /**
     * 迁移单个表的数据
     */
    protected function migrateTable($sourceTable, $targetTable)
    {
        // 获取数据库类型
        $dbType = config('database.connections.pboot.type');
        $this->createTable($sourceTable, $targetTable, $dbType);

        // 设置每批处理的数量
        $batchSize = 100;
        $offset = 0;

        // 获取总记录数
        $total = Db::connect('pboot')->table($sourceTable)->count();

        // 当 offset 小于总记录数时继续执行
        while ($offset < $total) {
            try {
                // 分批获取源表数据
                $data = Db::connect('pboot')
                    ->table($sourceTable)
                    ->limit($offset, $batchSize)
                    ->select()
                    ->toArray();

                if (!empty($data)) {
                    // 转换数据
                    $transformedData = $this->transformData($data, $sourceTable);
                    // 批量插入数据
                    Db::table($targetTable)->insertAll($transformedData);
                }

                // 无论是否有数据，都增加 offset
                $offset += $batchSize;

                // 释放内存
                unset($data, $transformedData);
                gc_collect_cycles();

            } catch (\Exception $e) {
                throw new \Exception('数据获取失败：' . $e->getMessage());
            }
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
            $targetPath = public_path().'static'.DIRECTORY_SEPARATOR.'upload';
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
        foreach ($data as $row) {
            $newRow = [];
            // 遍历目标表的所有字段
            foreach ($columns as $targetField => $info) {
                // 获取源字段名
                $sourceField = isset($this->fieldMap[$sourceTable][$targetField]) ?
                    $this->fieldMap[$sourceTable][$targetField] : $targetField;

                // 设置字段值
                if (isset($row[$sourceField])) {
                    $value = $row[$sourceField];

                    // 处理可能超长的字段
                    if (is_string($value)) {
                        $type = strtoupper($info['type']);
                        if (preg_match('/(VARCHAR|CHAR|TEXT|TINYTEXT|MEDIUMTEXT)\((\d+)\)?/i', $type, $matches)) {
                            $fieldType = strtoupper($matches[1]);
                            $length = isset($matches[2]) ? intval($matches[2]) : 0;

                            // 根据字段类型处理值
                            if (($fieldType === 'VARCHAR' || $fieldType === 'CHAR')) {
                                // 使用 mb_strwidth 获取字符串实际显示宽度
                                if (mb_strwidth($value) > $length) {
                                    $value = mb_strimwidth($value, 0, $length, '', 'UTF-8');
                                }
                            } elseif ($fieldType === 'TINYTEXT' && mb_strlen($value) > 255) {
                                $value = mb_substr($value, 0, 255);
                            }
                        }
                    }
                    $newRow[$targetField] = $value;
                } else {
                    // 设置默认值
                    if (isset($info['default'])) {
                        $newRow[$targetField] = $info['default'];
                    } else {
                        // 根据字段类型设置空值或处理超出范围的值
                        $type = strtoupper($info['type']);
                        if (preg_match('/INT\((\d+)\)/i', $type, $matches) || preg_match('/(TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT)/i', $type)) {
                            $maxValues = [
                                'TINYINT' => 127,
                                'SMALLINT' => 32767,
                                'MEDIUMINT' => 8388607,
                                'INT' => 2147483647,
                                'BIGINT' => PHP_INT_MAX
                            ];

                            $maxValue = PHP_INT_MAX;
                            foreach ($maxValues as $intType => $maxVal) {
                                if (stripos($type, $intType) !== false) {
                                    $maxValue = $maxVal;
                                    break;
                                }
                            }

                            $value = isset($row[$sourceField]) ? intval($row[$sourceField]) : 0;
                            // 如果值超出范围，设置为最大值
                            $newRow[$targetField] = min($value, $maxValue);
                        } elseif (preg_match('/(TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT)/i', $type)) {
                            // 处理没有指定长度的整数类型
                            $maxValues = [
                                'TINYINT' => 127,
                                'SMALLINT' => 32767,
                                'MEDIUMINT' => 8388607,
                                'INT' => 2147483647,
                                'BIGINT' => PHP_INT_MAX
                            ];
                            foreach ($maxValues as $intType => $maxVal) {
                                if (stripos($type, $intType) !== false) {
                                    $value = isset($row[$sourceField]) ? intval($row[$sourceField]) : 0;
                                    $newRow[$targetField] = min($value, $maxVal);
                                    break;
                                }
                            }
                        } else {
                            $newRow[$targetField] = isset($row[$sourceField]) ? intval($row[$sourceField]) : 0;
                        }
                    }
                }
            }

            $transformedData[] = $newRow;
        }

        return $transformedData;
    }
}

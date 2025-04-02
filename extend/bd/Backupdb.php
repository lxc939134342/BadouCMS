<?php

namespace bd;

use ba\Random;
use think\facade\Db;

class Backupdb
{
    /**
     * 数据库配置
     * @var string
     */
    private static string $database_config = "mysql";

    /**
     * 数据库对象
     * @var
     */
    protected $db;

    /**
     * 数据库备份构造方法
     * @param array $config 备份配置信息
     */
    public function __construct(array $config = [])
    {
        if (isset($config['database_config'])) {
            self::$database_config = $config['database_config'];
        }

        $this->db = Db::connect(self::$database_config);
    }

    // 数据库表状态列表
    public function getList()
    {
        return $this->db->query('SHOW TABLE STATUS');
    }

    // 获取全部表
    public function getTables()
    {
        $tableList = $this->db->query("SHOW TABLE STATUS");
        if (empty($tableList)) {
            return [];
        }

        return array_map(function ($val) {
            return [
                "table_name"  => $val['Name'],
                "engine"      => $val['Engine'],
                "collation"   => $val['Collation'],
                "version"     => $val['Version'],
                "rows"        => $val['Rows'],
                "create_time" => $val['Create_time'],
                "update_time" => $val['Update_time'],
                "size"        => format_bytes($val['Data_length']),
                "bredundancy" => format_bytes($val['Data_free']),
                "comment"     => $val['Comment'],

            ];
        }, $tableList);
    }

    // 数据库表优化
    public function optimize($tables)
    {
        return $this->db->query("OPTIMIZE TABLE $tables");
    }

    // 数据库表修复
    public function repair($tables)
    {
        return $this->db->query("REPAIR TABLE $tables");
    }

    // 备份数据表
    public function backupTable($tables)
    {
        $backdir = date('YmdHis');
        foreach ($tables as $table) {
            $sql = '';
            $sql .= $this->header(); // 备份文件头部说明
            $sql .= $this->tableSql($table); // 表结构信息
            $fields = $this->getFields($table); // 表字段
            $field_num = count($fields); // 字段数量
            $all_data = Db::table($table)->select(); // 读取全部数据
            $sql .= $this->dataSql($table, $fields, $field_num, $all_data); // 生成语句
            $filename = $backdir . "/" . Random::uuid() . "_" . $backdir . "_" . $table . '.sql'; // 写入文件
            $result = $this->writeFile($filename, $sql);
        }
        return $result;
    }

    // 插入数据库备份基础信息
    private function header()
    {
        $dbname = config('database.connections.mysql.database');
        $sql = '-- Online Database Management SQL Dump' . PHP_EOL;
        $sql .= '-- 数据库名: ' . $dbname . PHP_EOL;
        $sql .= '-- 生成日期: ' . date('Y-m-d H:i:s') . PHP_EOL;
        $sql .= '-- PHP 版本: ' . phpversion() . PHP_EOL . PHP_EOL;

        $sql .= 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";' . PHP_EOL;
        $sql .= 'SET time_zone = "+08:00";' . PHP_EOL;
        $sql .= 'SET NAMES utf8;' . PHP_EOL . PHP_EOL;

        $sql .= '-- --------------------------------------------------------' . PHP_EOL . PHP_EOL;
        return $sql;
    }

    // 备份整个数据库
    public function backupDB()
    {
        $sql = '';
        $sql .= $this->header(); // 备份文件头部说明
        $sql .= $this->dbSql(); // 数据库创建语句

        $tables = $this->getTables(); // 获取所有表
        foreach ($tables as $table) { // 表结构及数据
            $table_name = $table['table_name'];
            $sql .= $this->tableSql($table_name); // 表结构信息
            $fields = $this->getFields($table_name); // 表字段
            $field_num = count($fields); // 字段数量
            $all_data = Db::table($table_name)->select(); // 读取全部数据
            if ($all_data) {
                $sql .= $this->dataSql($table_name, $fields, $field_num, $all_data); // 生成数据语句
            }
            $sql .= '-- --------------------------------------------------------' . PHP_EOL . PHP_EOL;
        }
        $dbname = config('database.connections.mysql.database');
        // 写入文件
        $filename = Random::uuid() . '_' . date('YmdHis') . '_' . $dbname . '.sql';
        return $this->writeFile($filename, $sql);
    }

    // 数据库创建语句
    private function dbSql()
    {
        $dbname = config('database.connections.mysql.database');
        $sql = '';
        $sql .= "--" . PHP_EOL;
        $sql .= "-- 数据库名 `" . $dbname . '`' . PHP_EOL;
        $sql .= "--" . PHP_EOL . PHP_EOL;

        // 如果数据库不存在则创建
        $sql .= "CREATE DATABASE IF NOT EXISTS `" . $dbname . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;' . PHP_EOL;
        // 选择数据库
        $sql .= "USE `" . $dbname . "`;" . PHP_EOL . PHP_EOL;
        $sql .= '-- --------------------------------------------------------' . PHP_EOL . PHP_EOL;
        return $sql;
    }

    // 表结构语句
    private function tableSql($table)
    {
        $sql = '';
        $sql .= "--" . PHP_EOL;
        $sql .= "-- 表的结构 `" . $table . '`' . PHP_EOL;
        $sql .= "--" . PHP_EOL . PHP_EOL;

        $sql .= $this->tableStru($table); // 表创建语句
        return $sql;
    }

    // 数据语句
    private function dataSql($table, $fields, $fieldNnum, $data)
    {
        if (! $data) {
            return;
        }
        $sql = '';
        $sql .= "--" . PHP_EOL;
        $sql .= "-- 转存表中的数据 `" . $table . "`" . PHP_EOL;
        $sql .= "--" . PHP_EOL;
        $sql .= PHP_EOL;

        // 循环每个字段下面的内容
        $sql .= "INSERT INTO `" . $table . "` (" . implode(',', $fields) . ") VALUES" . PHP_EOL;
        $brackets = "(";
        foreach ($data as $value) {
            $row = array_values($value);
            $sql .= $brackets;
            $comma = "";
            for ($i = 0; $i < $fieldNnum; $i++) {
                $value = decode_string($row[$i]) ?? '';
                $sql .= ($comma . "'" . addslashes($value) . "'");
                $comma = ",";
            }
            $sql .= ")";
            $brackets = "," . PHP_EOL . "(";
        }
        $sql .= ';' . PHP_EOL . PHP_EOL;
        return $sql;
    }

    // 数据库表结构
    public function tableStru($table)
    {
        $sql = "DROP TABLE IF EXISTS `" . $table . '`;' . PHP_EOL;
        $result = $this->db->query('SHOW CREATE TABLE `' . $table . '`');
        return $sql . $result[0]['Create Table'] . ';' . PHP_EOL . PHP_EOL;
    }

    // 获取表字段名
    public function getFields($table)
    {
        $one_data = $this->db->query("SELECT * FROM " . $table); // 读取数据
        $fields = array();
        if ($one_data) {
            foreach ($one_data[0] as $key => $value) {
                $fields[] = "`$key`";
            }
        }
        return $fields;
    }

    // 写入文件
    private function writeFile($filename, $content)
    {
        $sqlfile = root_path() . 'backup' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . $filename;
        check_file($sqlfile, true);
        if (file_put_contents($sqlfile, $content)) {
            return true;
        }
    }
}

<?php

namespace app\common\service;

use badou\TableManager;
use Phinx\Db\Adapter\AdapterInterface;
use think\facade\Db;

/**
 * 可视化建表：基于 Phinx 适配器创建、修改数据表与字段
 */
class TableDesignService
{
    /**
     * 不允许通过设计器改动的系统表
     */
    protected array $systemTables = [
        'admin', 'admin_log', 'admin_rule', 'admin_group', 'admin_group_access',
        'config', 'attachment', 'user', 'user_group', 'user_rule', 'user_level', 'user_token',
    ];

    protected array $typeMap = [
        'varchar' => ['type' => AdapterInterface::PHINX_TYPE_STRING, 'limit' => 255],
        'char' => ['type' => AdapterInterface::PHINX_TYPE_CHAR, 'limit' => 50],
        'text' => ['type' => AdapterInterface::PHINX_TYPE_TEXT, 'limit' => null],
        // 长度阈值在 MysqlAdapter 上，接口没有这两个常量
        'mediumtext' => ['type' => AdapterInterface::PHINX_TYPE_TEXT, 'limit' => 16777215],
        'longtext' => ['type' => AdapterInterface::PHINX_TYPE_TEXT, 'limit' => 2147483647],
        'int' => ['type' => AdapterInterface::PHINX_TYPE_INTEGER, 'limit' => null],
        'tinyint' => ['type' => AdapterInterface::PHINX_TYPE_TINY_INTEGER, 'limit' => null],
        'smallint' => ['type' => AdapterInterface::PHINX_TYPE_SMALL_INTEGER, 'limit' => null],
        'bigint' => ['type' => AdapterInterface::PHINX_TYPE_BIG_INTEGER, 'limit' => null],
        'decimal' => ['type' => AdapterInterface::PHINX_TYPE_DECIMAL, 'limit' => null],
        'float' => ['type' => AdapterInterface::PHINX_TYPE_FLOAT, 'limit' => null],
        'double' => ['type' => AdapterInterface::PHINX_TYPE_DOUBLE, 'limit' => null],
        'date' => ['type' => AdapterInterface::PHINX_TYPE_DATE, 'limit' => null],
        'datetime' => ['type' => AdapterInterface::PHINX_TYPE_DATETIME, 'limit' => null],
        'timestamp' => ['type' => AdapterInterface::PHINX_TYPE_TIMESTAMP, 'limit' => null],
        'enum' => ['type' => AdapterInterface::PHINX_TYPE_ENUM, 'limit' => null],
        'set' => ['type' => AdapterInterface::PHINX_TYPE_SET, 'limit' => null],
    ];

    public function typeOptions(): array
    {
        return array_keys($this->typeMap);
    }

    /**
     * 去掉前缀后的表名，并校验标识符合法
     */
    public function bareName(string $name): string
    {
        $name = strtolower(trim($name));
        $prefix = $this->prefix();
        if ($prefix !== '' && stripos($name, $prefix) === 0) {
            $name = substr($name, strlen($prefix));
        }
        if (!preg_match('/^[a-z][a-z0-9_]{0,40}$/', $name)) {
            throw new \InvalidArgumentException('表名只能使用小写字母、数字和下划线，且以字母开头');
        }
        return $name;
    }

    public function fieldName(string $name): string
    {
        $name = strtolower(trim($name));
        if (!preg_match('/^[a-z][a-z0-9_]{0,40}$/', $name)) {
            throw new \InvalidArgumentException('字段名只能使用小写字母、数字和下划线，且以字母开头');
        }
        return $name;
    }

    public function assertDesignable(string $bare): void
    {
        if (in_array($bare, $this->systemTables, true)) {
            throw new \RuntimeException('系统表不允许通过设计器修改');
        }
    }

    public function exists(string $bare): bool
    {
        return TableManager::phinxTable($bare)->exists();
    }

    /**
     * 创建数据表，默认带自增主键 id
     */
    public function createTable(string $name, string $comment, string $engine, array $fields): void
    {
        $bare = $this->bareName($name);
        $this->assertDesignable($bare);
        if ($this->exists($bare)) {
            throw new \RuntimeException('数据表已存在');
        }
        if (!$fields) {
            throw new \InvalidArgumentException('请至少添加一个字段');
        }

        $primary = [];
        $columns = [];
        foreach ($fields as $field) {
            [$column, $type, $options, $index] = $this->column($field);
            $columns[] = [$column, $type, $options, $index, !empty($field['primary'])];
            if (!empty($field['primary'])) {
                $primary[] = $column;
            }
        }
        if (!$primary) {
            throw new \InvalidArgumentException('请设置一个主键字段');
        }

        // 主键只能通过建表选项指定，addIndex 不接受 primary
        $table = TableManager::phinxTable($bare, [
            'engine' => in_array($engine, ['InnoDB', 'MyISAM'], true) ? $engine : 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
            'comment' => $comment,
            'id' => false,
            'primary_key' => $primary,
            'signed' => false,
        ]);
        foreach ($columns as [$column, $type, $options, $index]) {
            $table->addColumn($column, $type, $options);
            if ($index && !in_array($column, $primary, true)) {
                $table->addIndex($column, $index === 'unique' ? ['unique' => true] : []);
            }
        }
        $table->create();
    }

    /**
     * 修改表注释与引擎
     */
    public function updateTable(string $name, string $comment, string $engine): void
    {
        $bare = $this->bareName($name);
        $this->assertDesignable($bare);
        $full = TableManager::tableName($bare, true);
        $engine = in_array($engine, ['InnoDB', 'MyISAM'], true) ? $engine : 'InnoDB';
        Db::execute(sprintf(
            'ALTER TABLE `%s` ENGINE = %s COMMENT = %s',
            str_replace('`', '', $full),
            $engine,
            Db::connect()->getPdo()->quote($comment)
        ));
    }

    public function dropTable(string $name): void
    {
        $bare = $this->bareName($name);
        $this->assertDesignable($bare);
        $table = TableManager::phinxTable($bare);
        if (!$table->exists()) {
            throw new \RuntimeException('数据表不存在');
        }
        $table->drop()->save();
    }

    public function addField(string $table, array $field): void
    {
        $bare = $this->bareName($table);
        $this->assertDesignable($bare);
        [$column, $type, $options, $index] = $this->column($field);
        $phinx = TableManager::phinxTable($bare);
        if ($phinx->hasColumn($column)) {
            throw new \RuntimeException('字段已存在');
        }
        $phinx->addColumn($column, $type, $options);
        if ($index) {
            $phinx->addIndex($column, $index === 'unique' ? ['unique' => true] : []);
        }
        $phinx->update();
    }

    public function changeField(string $table, string $origin, array $field): void
    {
        $bare = $this->bareName($table);
        $this->assertDesignable($bare);
        $origin = $this->fieldName($origin);
        [$column, $type, $options] = $this->column($field);
        $phinx = TableManager::phinxTable($bare);
        if (!$phinx->hasColumn($origin)) {
            throw new \RuntimeException('原字段不存在');
        }
        if ($column !== $origin && $phinx->hasColumn($column)) {
            throw new \RuntimeException('目标字段名已存在');
        }
        $phinx->changeColumn($origin, $type, $options);
        if ($column !== $origin) {
            $phinx->renameColumn($origin, $column);
        }
        $phinx->update();
    }

    public function dropField(string $table, string $field): void
    {
        $bare = $this->bareName($table);
        $this->assertDesignable($bare);
        $field = $this->fieldName($field);
        $columns = TableManager::getTableColumns($bare);
        if (!isset($columns[$field])) {
            throw new \RuntimeException('字段不存在');
        }
        if (($columns[$field]['COLUMN_KEY'] ?? '') === 'PRI') {
            throw new \RuntimeException('主键字段不允许删除');
        }
        $phinx = TableManager::phinxTable($bare);
        $phinx->removeColumn($field)->update();
    }

    /**
     * 将表单字段转成 Phinx 列定义
     * @return array{0:string,1:string,2:array,3:string}
     */
    protected function column(array $field): array
    {
        $name = $this->fieldName((string)($field['name'] ?? ''));
        $dataType = strtolower((string)($field['type'] ?? ''));
        if (!isset($this->typeMap[$dataType])) {
            throw new \InvalidArgumentException('不支持的字段类型：' . $dataType);
        }
        $mapped = $this->typeMap[$dataType];
        $options = [
            'null' => empty($field['required']),
            'comment' => mb_substr(trim((string)($field['comment'] ?? '')), 0, 255),
            'signed' => !empty($field['signed']),
        ];
        if ($mapped['limit'] !== null && !in_array($dataType, ['decimal'], true)) {
            $options['limit'] = $mapped['limit'];
        }
        $length = (int)($field['length'] ?? 0);
        if ($length > 0 && in_array($dataType, ['varchar', 'char', 'int', 'tinyint', 'bigint'], true)) {
            $options['limit'] = min($length, $dataType === 'varchar' ? 5000 : 255);
        }
        if ($dataType === 'decimal') {
            $options['precision'] = max(1, min((int)($field['precision'] ?? 10), 65));
            $options['scale'] = max(0, min((int)($field['scale'] ?? 2), 30));
        }
        if (in_array($dataType, ['enum', 'set'], true)) {
            $values = array_values(array_filter(array_map('trim', explode(',', (string)($field['values'] ?? '')))));
            if (count($values) < 1) {
                throw new \InvalidArgumentException('枚举类型必须填写可选值，多个用英文逗号分隔');
            }
            $options['values'] = $values;
        }
        if (!empty($field['primary']) && in_array($dataType, ['int', 'bigint'], true)) {
            $options['identity'] = true;
            $options['null'] = false;
            $options['signed'] = false;
        }
        $default = $field['default'] ?? null;
        if ($default !== null && $default !== '' && empty($options['identity'])) {
            $options['default'] = in_array($dataType, ['int', 'tinyint', 'smallint', 'bigint', 'decimal', 'float', 'double'], true)
                ? $default + 0
                : (string)$default;
        }

        $index = '';
        if (empty($field['primary']) && in_array($field['index'] ?? '', ['index', 'unique'], true)) {
            $index = $field['index'];
        }
        return [$name, $mapped['type'], $options, $index];
    }

    protected function prefix(): string
    {
        return (string)config('database.connections.' . config('database.default') . '.prefix');
    }
}

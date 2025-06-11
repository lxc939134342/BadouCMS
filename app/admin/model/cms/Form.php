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

namespace app\admin\model\cms;

use badou\TableManager;
use think\facade\Db;
use think\Model;

/**
 * Form
 */
class Form extends Model
{
    // 表名
    protected $name = 'cms_form';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    //默认主键为id，如果你没有使用id作为主键名，需要在模型中设置属性
    protected $pk = 'id';

    /* 插入前 */
    public static function onBeforeInsert($model)
    {
        $table_name = $model->getData('table_name');
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
            throw new \think\Exception('表名称必须以包含字母、数字、下划线');
        }

        $dbPrefix = config('database.connections.mysql.prefix').'cms_diy_';
        if ($dbPrefix && strpos($table_name, $dbPrefix) !== 0) {
            $model->table_name = $dbPrefix . $table_name;
        }

        $count = Db::name($model->getName())
            ->where('table_name', $model->table_name)->count();
        if ($count) {
            throw new \think\Exception('表名称已存在');
        }
        $model->fcode = Db::name($model->getName())->max('fcode') + 1;
    }

    /* 插入后 */
    public static function onAfterInsert($model)
    {
        $data = $model->getData();
        $table_name = $data['table_name'];
        $databaseConnection = config('database.default');
        // 创建表
        $tableManager = TableManager::phinxTable($table_name, [
            'id'          => false,
            'comment'     => '',
            'row_format'  => 'DYNAMIC',
            'primary_key' => 'id',
            'collation'   => 'utf8mb4_unicode_ci',
        ], false, $databaseConnection);

        // 添加id字段
        $tableManager->addColumn('id', 'integer', [
            'identity' => true,
            'signed' => false,
            'limit' => 10,
            'comment' => '主键ID'
        ]);

        // 添加acode字段
        $tableManager->addColumn('acode', 'string', [
            'limit' => 20,
            'collation' => 'utf8mb4_unicode_ci',
            'null' => false,
            'comment' => '区域编码'
        ]);

        // 添加create_time字段
        $tableManager->addColumn('create_time', 'datetime', [
            'null' => false,
            'comment' => '创建时间'
        ]);

        $tableManager->create();
    }

    /* 更新前 */
    public static function onBeforeUpdate($model)
    {
        $data = $model->getData();
        $changeData = $model->getChangedData();
        $originData = $model->getOrigin();
        $oldname = $originData['table_name'];
        $field = $data['table_name'];
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
            throw new \think\Exception('表名称必须以包含字母、数字、下划线');
        }
        $where = [
            ['id', '<>', $data['id']],
            ['table_name', '=',  $field]
        ];
        $count = Db::name($model->getName())->where($where)->count();
        if ($count) {
            throw new \think\Exception('表名称已存在');
        }
    }
}

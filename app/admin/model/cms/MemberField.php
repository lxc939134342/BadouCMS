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

use ba\TableManager;
use think\facade\Db;
use think\Model;

/**
 * MemberField
 */
class MemberField extends Model
{
    // 表名
    protected $name = 'cms_member_field';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    /* 表链接 */
    protected static function tableManager()
    {
        $databaseConnection = config('database.default');
        $adapter      = TableManager::phinxAdapter(false, $databaseConnection);
        $databaseConfig = config('database.connections');
        $table_name = $databaseConfig[$databaseConnection]['prefix'] . "user";
        if ($adapter->hasTable($table_name)) {
            $tableManager = TableManager::phinxTable($table_name, [], false, $databaseConnection);
            return $tableManager;
        } else {
            throw new \think\Exception('请先创建表');
        }
    }

    /* 插入前 */
    public static function onBeforeInsert($model)
    {
        $data = $model->getData();
        $field = $data['name'];
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $field)) {
            throw new \think\Exception('字段名必须以字母开头,且只能包含字母、数字、下划线');
        }
        $count = Db::name($model->getName())->where('name', $field)->count();
        if ($count) {
            throw new \think\Exception('字段已存在');
        }
        $tableManager = self::tableManager();
        if ($tableManager->hasColumn($field)) {
            throw new \think\Exception('字段在会员表中已存在');
        } else {
            $tableManager->addColumn($field, 'string', ['limit' => $data['length'], 'default' => '']);
        }

        $tableManager->update();
    }

    /* 更新前 */
    public static function onBeforeUpdate($model)
    {
        $data = $model->getData();
        $changeData = $model->getChangedData();
        $originData = $model->getOrigin();
        $oldname = $originData['name'];
        $field = $data['name'];
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $field)) {
            throw new \think\Exception('字段名必须以`ext_`开头,且只能包含字母、数字、下划线');
        }
        $where = [
            ['id', '<>', $data['id']],
            ['name', '=',  $field]
        ];
        $count = Db::name($model->getName())->where($where)->count();
        if ($count) {
            throw new \think\Exception('字段已存在');
        }

        $tableManager = self::tableManager();
        if (!$tableManager->hasColumn($oldname)) {
            $tableManager->addColumn($field, 'string', ['limit' => $data['length'], 'default' => '']);
            $tableManager->update();
        }

        if (isset($changeData['name'])) {
            $tableManager->renameColumn($oldname, $field);
        }

        if (isset($changeData['length'])) {
            $tableManager->changeColumn($oldname, 'string', ['limit' => $data['length'], 'default' => '']);
        }

        if (isset($changeData['length']) || isset($changeData['name'])) {
            $tableManager->update();
        }
    }

    /* 删除后 */
    public static function onAfterDelete($model)
    {
        $data = $model->getData();
        $tableManager = self::tableManager();
        if (!$tableManager->hasColumn($data['name'])) {
            throw new \think\Exception('字段不存在');
        }
        $tableManager->removeColumn($data['name']);
        $tableManager->update();
    }
}

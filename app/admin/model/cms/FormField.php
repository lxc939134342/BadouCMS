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

use think\Model;
use ba\TableManager;
use think\facade\Db;

/**
 * Field
 */
class FormField extends Model
{
    // 表名
    protected $name = 'cms_form_field';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    /* 插入前 */
    public static function onBeforeInsert($model)
    {
        $data = $model->getData();
        $fcode = $data['fcode'];
        $field = $data['name'];
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
            throw new \think\Exception('字段名必须以包含字母、数字、下划线');
        }

        $where = [
            ['name', '=',  $field],
            ['fcode', '=',  $fcode]
        ];

        $count = Db::name($model->getName())->where($where)->count();
        if ($count) {
            throw new \think\Exception('字段已存在');
        }
    }

    /* 更新前 */
    public static function onBeforeUpdate($model)
    {
        $data = $model->getData();
        $changeData = $model->getChangedData();
        $originData = $model->getOrigin();
        $oldname = $originData['name'];
        $fcode = $data['fcode'];
        $field = $data['name'];
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
            throw new \think\Exception('字段名必须以包含字母、数字、下划线');
        }
        $where = [
            ['id', '<>', $data['id']],
            ['name', '=',  $field],
            ['fcode', '=',  $fcode]
        ];
        $count = Db::name($model->getName())->where($where)->count();
        if ($count) {
            throw new \think\Exception('字段已存在');
        }

        $tableManager = self::tableManager($fcode);
        if (!$tableManager->hasColumn($oldname)) {
            throw new \think\Exception('字段不存在');
        }

        if (isset($changeData['name'])) {
            $tableManager->renameColumn($oldname, $field);
        }
    }


    /* 表链接 */
    protected static function tableManager($fcode)
    {
        $databaseConnection = config('database.default');
        $adapter      = TableManager::phinxAdapter(false, $databaseConnection);
        $table_name = Db::name('cms_form')->where('fcode', $fcode)->value('table_name');
        if ($adapter->hasTable($table_name)) {
            $tableManager = TableManager::phinxTable($table_name, [], false, $databaseConnection);
            return $tableManager;
        } else {
            throw new \think\Exception('请先创建扩展表');
        }
    }

}

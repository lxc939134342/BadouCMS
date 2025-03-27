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
        $data = $model->getData();
        $field = $data['table_name'];
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
            throw new \think\Exception('表名称必须以包含字母、数字、下划线');
        }
        $count = Db::name($model->getName())->where('table_name', $field)->count();
        if ($count) {
            throw new \think\Exception('表名称已存在');
        }
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

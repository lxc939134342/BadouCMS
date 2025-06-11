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
 * Extfield
 */
class Extfield extends Model
{
    // 表名
    protected $name = 'cms_extfield';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    protected $append = [
        'content'
    ];

    public function getContentAttr($value, $data)
    {
        if (!$data['value']) {
            return [];
        }
        $value = explode(',', $data['value']);
        $data = [];
        foreach ($value as $item) {
            $data[$item] = $item;
        }
        return $data;
    }

    /* 类型列表 */
    public function typeList(): array
    {
        $options = [
            '1'  => ['text' => '单行文本', 'designType' => 'string', 'type' => 'string', 'limit' => 100, 'default' => ''],
            '2'  => ['text' => '多行文本', 'designType' => 'textarea', 'type' => 'string', 'limit' => 1000, 'default' => ''],
            '3'  => ['text' => '单选按钮', 'designType' => 'radio', 'type' => 'string', 'limit' => 255, 'default' => ''],
            '4'  => ['text' => '多选按钮', 'designType' => 'checkbox', 'type' => 'string', 'limit' => 255, 'default' => ''],
            '5'  => ['text' => '单图上传', 'designType' => 'image', 'type' => 'string', 'limit' => 255, 'default' => ''],
            '10' => ['text' => '多图上传', 'designType' => 'images', 'type' => 'string', 'limit' => 1000, 'default' => ''],
            '6'  => ['text' => '附件上传', 'designType' => 'files', 'type' => 'string', 'limit' => 255, 'default' => ''],
            '7'  => ['text' => '日期选择', 'designType' => 'datetime', 'type' => 'datetime', 'limit' => 0, 'default' => null],
            '8'  => ['text' => '编辑器', 'designType' => 'editor', 'type' => 'text', 'limit' => 0, 'default' => null],
            '9'  => ['text' => '下拉选择', 'designType' => 'select', 'type' => 'string', 'limit' => 255, 'default' => '']
        ];

        return $options;
    }

    /* 类型文字 */
    public function typeListTextMap(): array
    {
        $map = [];
        $list = $this->typeList();
        foreach ($list as $key => $value) {
            $map[$key] = $value['text'];
        }
        return $map;
    }

    /* 类型组件 */
    public function typeListComponentMap(): array
    {
        $map = [];
        $list = $this->typeList();
        foreach ($list as $key => $value) {
            $map[$key] = $value['designType'];
        }
        return $map;
    }

    /* 表链接 */
    protected static function tableManager()
    {
        $databaseConnection = config('database.default');
        $adapter      = TableManager::phinxAdapter(false, $databaseConnection);
        $databaseConfig = config('database.connections');
        $table_name = $databaseConfig[$databaseConnection]['prefix'] . "cms_content_ext";
        if ($adapter->hasTable($table_name)) {
            $tableManager = TableManager::phinxTable($table_name, [], false, $databaseConnection);
            return $tableManager;
        } else {
            throw new \think\Exception('请先创建扩展表');
        }
    }

    /* 插入前 */
    public static function onBeforeInsert($model)
    {
        $data = $model->getData();
        $value = $data['value'] ?? '';
        $sorting = $data['sorting'] ?? 0;
        $model->set('value', $value);
        $model->set('sorting', $sorting);

        $field = $data['name'];
        if (!preg_match('/^ext_[a-zA-Z0-9_]+$/', $field)) {
            throw new \think\Exception('字段名必须以`ext_`开头,且只能包含字母、数字、下划线');
        }
        $count = Db::name($model->getName())->where('name', $field)->count();
        if ($count) {
            throw new \think\Exception('字段已存在');
        }
        $tableManager = self::tableManager();
        if ($tableManager->hasColumn($field)) {
            throw new \think\Exception('字段已存在');
        } else {
            $typeList = $model->typeList();
            $typeRow = $typeList[$data['type']];
            $tableManager->addColumn($field, $typeRow['type'], ['limit' => $typeRow['limit'], 'default' => $typeRow['default']]);
        }

        $tableManager->update();
    }

    /* 更新前 */
    public static function onBeforeUpdate($model)
    {
        $data = $model->getData();
        $value = $data['value'] ?? '';
        $sorting = $data['sorting'] ?? 0;
        $model->set('value', $value);
        $model->set('sorting', $sorting);
        $changeData = $model->getChangedData();
        $originData = $model->getOrigin();
        $oldname = $originData['name'];
        $field = $data['name'];
        if (!preg_match('/^ext_[a-zA-Z0-9_]+$/', $field)) {
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
            throw new \think\Exception('字段不存在');
        }

        if (isset($changeData['name'])) {
            $tableManager->renameColumn($oldname, $field);
        }

        if (isset($changeData['type'])) {
            $typeList = $model->typeList();
            $typeRow = $typeList[$data['type']];
            $tableManager->changeColumn($oldname, $typeRow['type'], ['limit' => $typeRow['limit'], 'default' => $typeRow['default']]);
        }

        if (isset($changeData['type']) || isset($changeData['name'])) {
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

    public function getModelFields(string $mcode): array
    {
        $where = [
            'mcode' => $mcode,
        ];
        $res = $this->where($where)->order('sorting ASC,id DESC')->select();
        if ($res->isEmpty()) {
            return [];
        }
        $data = $res->toArray();
        $typeListComponentMap = $this->typeListComponentMap();
        foreach ($data as $k => $v) {
            $data[$k]['component'] = $typeListComponentMap[$v['type']];
        }

        return $data;
    }
}

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
use app\common\library\Menu;
use app\admin\model\AdminRule;

/**
 * Models
 */
class Models extends Model
{
    // 表名
    protected $name = 'cms_model';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $webTranslate = '';

    protected $append = [
        'type_text'
    ];

    public function getTypeList()
    {
        return [
            1 => '单页',
            2 => '列表'
        ];
    }

    public function getTypeTextAttr($value, $data)
    {
        $list = $this->getTypeList();
        return $list[$data['type']] ?? '';
    }

    public static function onAfterWrite($model)
    {
        $data = $model->getData();
        $changeData = $model->getChangedData();

        // /* 菜单名称 */
        // if ($data['type'] == 1) {
        //     $menu_name = "cms.single/index/mcode/" . $data['mcode'];
        // } else {
        //     $menu_name = "cms.content/index/mcode/" . $data['mcode'];
        // }

        // /* 获取菜单数据 */
        // $menu_data = AdminRule::where('name', $menu_name)->find();
        // if (!$menu_data) {
        //     /* 创建菜单 */
        //     Menu::create([
        //         [
        //             'type'      => '1',
        //             'title'     => $data['name'] .__('Content'),
        //             'name'      => $menu_name,
        //             'menu_type' => '_iframe',
        //             'children'  => [],
        //         ]
        //     ], 'cms.content');
        // } else {
        //     /* 修改菜单 */
        //     if (isset($changeData['name'])) {
        //         $menu_data['title'] = $data['name'] .__('Content');
        //     }
        //     $menu_data->save();
        // }

        // /* 更新菜单状态 */
        // if (isset($changeData['status'])) {
        //     $status = $changeData['status'];
        //     if ($status == 1) {
        //         Menu::enable($menu_name);
        //     } else {
        //         Menu::disable($menu_name);
        //     }
        // }
    }

    public static function onAfterDelete($model): void
    {
        $data = $model->getData();
        // if ($data['type'] == 1) {
        //     $menu_name = 'cms.single/index/mcode/' . $data['mcode'];
        // } else {
        //     $menu_name = 'cms.content/index/mcode/' . $data['mcode'];
        // }
        // Menu::delete($menu_name);
    }

    public function getLastCode()
    {
        return $this->order('id DESC')->value('mcode');
    }

    public function getMcodesOfType($type)
    {
        return $this->where('type', $type)->order('id DESC')->column('mcode');
    }
}

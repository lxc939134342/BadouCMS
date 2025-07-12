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

namespace app\common\library;

use badou\Tree;
use think\facade\Db;
use app\admin\model\AdminRule;
use think\db\exception\PDOException;

class Menu
{
    /**
     * 创建菜单
     * @param array $menu
     * @param mixed $parent 父类的name或pid
     */
    public static function create($menu = [], $parent = 0)
    {
        $old = [];
        self::menuUpdate($menu, $old, $parent);
    }

    /**
     * 删除菜单
     * @param string $name 规则name
     * @return boolean
     */
    public static function delete($name)
    {
        $ids = self::getAdminRuleIdsByName($name);
        if (!$ids) {
            return false;
        }
        AdminRule::destroy($ids);
        return true;
    }

    /**
     * 启用菜单
     * @param string $name
     * @return boolean
     */
    public static function enable($name)
    {
        $ids = self::getAdminRuleIdsByName($name);
        if (!$ids) {
            return false;
        }
        AdminRule::where('id', 'in', $ids)->update(['status' => 'normal']);
        return true;
    }

    /**
     * 禁用菜单
     * @param string $name
     * @return boolean
     */
    public static function disable($name)
    {
        $ids = self::getAdminRuleIdsByName($name);
        if (!$ids) {
            return false;
        }
        AdminRule::where('id', 'in', $ids)->update(['status' => 'hidden']);
        return true;
    }

    /**
     * 升级菜单
     * @param string $name 插件名称
     * @param array  $menu 新菜单
     * @return bool
     */
    public static function upgrade($name, $menu)
    {
        $ids = self::getAdminRuleIdsByName($name);
        $old = AdminRule::where('id', 'in', $ids)->select();
        $old = $old->toArray();
        $old = array_column($old, null, 'name');

        Db::startTrans();
        try {
            self::menuUpdate($menu, $old);
            $ids = [];
            foreach ($old as $index => $item) {
                if (!isset($item['keep'])) {
                    $ids[] = $item['id'];
                }
            }
            if ($ids) {
                $menus = $config['menus'] ?? [];
                $where = ['id' => ['in', $ids]];
                if ($menus) {
                    //必须是旧版本中的菜单,可排除用户自主创建的菜单
                    $where['name'] = ['in', $menus];
                }
                AdminRule::where($where)->delete();
            }

            Db::commit();
        } catch (PDOException $e) {
            Db::rollback();
            return false;
        }

        return true;
    }

    /**
     * 导出指定名称的菜单规则
     * @param string $name
     * @return array
     */
    public static function export($name)
    {
        $ids = self::getAdminRuleIdsByName($name);
        if (!$ids) {
            return [];
        }
        $menuList = [];
        $menu = AdminRule::getByName($name);
        if ($menu) {
            $ruleList = AdminRule::where('id', 'in', $ids)->field('name,id,pid,type,title,icon,ismenu,py,pinyin')->select()->toArray();
            $menuList = Tree::instance()->init($ruleList, 'pid', null, 'id', 'sublist')->getTreeArray($menu['id']);
        }
        return $menuList;
    }

    /**
     * 菜单升级
     * @param array $newMenu
     * @param array $oldMenu
     * @param int|string   $parent
     */
    public static function menuUpdate($newMenu, &$oldMenu, $parent = 0)
    {
        if (!is_numeric($parent)) {
            $parentRule = AdminRule::getByName($parent);
            $pid = $parentRule ? $parentRule['id'] : 0;
        } else {
            $pid = $parent;
        }
        $allow = array_flip(['name', 'title', 'url', 'icon', 'condition', 'remark', 'ismenu', 'menutype', 'extend', 'weigh', 'status','type']);
        foreach ($newMenu as $k => $v) {
            $hasChild = isset($v['sublist']) && $v['sublist'];
            $data = array_intersect_key($v, $allow);
            $data['ismenu'] = $data['ismenu'] ?? ($hasChild ? 1 : 0);
            $data['icon'] = $data['icon'] ?? ($hasChild ? 'fa fa-list' : 'fa fa-circle-o');
            $data['pid'] = $pid;
            $data['status'] = $data['status'] ?? 'normal';
            if (!isset($oldMenu[$data['name']])) {
                $menu = AdminRule::create($data);
            } else {
                $menu = $oldMenu[$data['name']];
                //更新旧菜单
                AdminRule::update($data, ['id' => $menu['id']]);
                $oldMenu[$data['name']]['keep'] = true;
            }
            if ($hasChild) {
                self::menuUpdate($v['sublist'], $oldMenu, $menu['id']);
            }
        }
    }

    /**
     * 根据名称获取规则IDS
     * @param string $name
     * @return array
     */
    public static function getAdminRuleIdsByName($name)
    {
        $ids = [];
        $menu = AdminRule::getByName($name);
        if ($menu) {
            // 必须将结果集转换为数组
            $ruleList = AdminRule::order('weigh', 'desc')->field('id,pid,name')->select()->toArray();
            // 构造菜单数据
            $ids = Tree::instance()->init($ruleList)->getChildrenIds($menu['id'], true);
        }
        return $ids;
    }

}

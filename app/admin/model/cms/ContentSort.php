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

use bd\Tree;
use think\Model;
use think\facade\Db;
use think\facade\Cache;

/**
 * ContentSort
 */
class ContentSort extends Model
{
    // 表名
    protected $name = 'cms_content_sort';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    public function models()
    {
        return $this->belongsTo(Models::class, 'mcode', 'mcode');
    }

    public static function onAfterInsert($model)
    {
        $data = $model->getData();
        $type = Db::name('cms_model')->where('mcode', $data['mcode'])->value('type');
        if ($type == 1 && ! $data['outlink']) { // 在填写了外链时不生成单页
            self::addSingle($data['scode'], $data['name']);
        }
        return false;
    }

    public static function onAfterUpdate($model)
    {
        $data = $model->getData();
        $type = Db::name('cms_model')->where('mcode', $data['mcode'])->value('type');
        $content = Db::name('cms_content')->where('scode', $data['scode'])->find();
        // 如果修改为单页并且跳转，则删除单页内容，否则判断是否存在内容，不存在则添加
        if ($type == 1 && $data['outlink']) {
            Db::name('cms_content')->where('scode', $data['scode'])->delete();
        } elseif ($type == 1 && ! $content) {
            self::addSingle($data['scode'], $data['name']);
        }
    }

    public static function onAfterWrite($model)
    {
        // 清除缓存
        Cache::tag('cms_cache')->clear();
    }

    public static function onAfterDelete($model)
    {
        /*同步删除内容*/
        Db::name('cms_content')->whereIn('scode', $model['scode'])->delete();
        // 清除缓存
        Cache::tag('cms_cache')->clear();
    }

    /**
     * 获取栏目的所有子节点ID
     * @param int  $scode       栏目scode
     * @param bool $withself 是否包含自身
     * @return array
     */
    public function getChildrenIds($scode, $withself = false, $is_all = false)
    {
        $where = [];
        if (!$is_all) {
            $where[] = ['status','=',1];
        }
        // 将 scode 做为 id
        $datalist = $this->where($where)->field('scode as id,pcode as pid')->select()->toArray();
        $tree = Tree::instance();
        $tree->init($datalist);
        $data = $tree->getChildrenIds($scode, $withself);
        return $data;
    }

    // 获取最后一个code
    public function getLastCode()
    {
        return $this->order('id DESC')->value('scode');
    }

    /**
     * 添加单页内容
     * @param string $scode
     * @param string $title
     * @return bool
     */
    public static function addSingle(string $scode, string $title): void
    {
        $auth = \app\admin\library\Auth::instance()->getInfo();
        // 构建数据
        $data = array(
            'acode' => get_backend_lang(),
            'scode' => $scode,
            'subscode' => '',
            'title' => $title,
            'titlecolor' => '#333333',
            'subtitle' => '',
            'filename' => '',
            'author' => $auth['nickname'],
            'source' => '本站',
            'outlink' => '',
            'date' => date('Y-m-d H:i:s'),
            'ico' => '',
            'pics' => '',
            'picstitle' => '',
            'content' => '',
            'tags' => '',
            'enclosure' => '',
            'keywords' => '',
            'description' => '',
            'sorting' => 255,
            'status' => 1,
            'istop' => 0,
            'isrecommend' => 0,
            'isheadline' => 0,
            'gid' => 0,
            'gtype' => 4,
            'gnote' => '',
            'visits' => 0,
            'likes' => 0,
            'oppose' => 0,
            'create_user' => $auth['nickname'],
            'update_user' => $auth['nickname'],
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
        );
        Db::name('cms_content')->insert($data);
    }


}

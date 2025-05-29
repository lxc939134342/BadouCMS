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
 * Content
 */
class Content extends Model
{
    // 表名
    protected $name = 'cms_content';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    protected $dateFormat = 'Y-m-d H:i:s';


    public function setContentAttr($value, $data): string
    {
        $value = replaceEditorDomain($value, request()->domain());
        /* 替换富文本中的图片域名 */
        return $value;
    }

    public function getContentAttr($value): string
    {
        /* 替换富文本中的图片域名 */
        $value = addEditorDomain($value, request()->domain());
        return !$value ? '' : htmlspecialchars_decode($value);
    }

    public function setPicsAttr($value, $data): string
    {
        return $value ? implode(',', $value) : '';
    }

    public function getPicsAttr($value): array
    {
        return $value ? explode(',', $value) : [];
    }

    public function contentsort()
    {
        return $this->belongsTo(ContentSort::class, 'scode', 'scode');
    }

    // 检查自定义URL名称
    public function checkFilename($filename, $where = array())
    {
        return $this->field('id')
            ->where("filename='$filename'")
            ->where($where)
            ->find();
    }

    /**
     * 复制内容
     * @param mixed $ids
     * @param mixed $scode
     * @return int|string
     */
    public function copyContent($ids, $scode): int|string
    {
        // 查找出要复制的主内容
        $data = $this->where('id', 'in', $ids)->select();

        foreach ($data as $key => $value) {
            $value = $value->toArray();
            // 查找扩展内容
            $extdata = Db::name('cms_content_ext')
                ->where('contentid=' . $value['id'])
                ->find();

            // 去除主键并修改栏目
            unset($value['id']);
            $value['scode'] = $scode;
            $value['date'] = date('Y-m-d H:i:s');
            $value['create_time'] = date('Y-m-d H:i:s');
            $value['update_time'] = date('Y-m-d H:i:s');
            $value['pics'] = $value['pics'] ? implode(',', $value['pics']) : '';
            // 插入主内容
            $id = $this->insertGetId($value);

            // 插入扩展内容
            if ($id && $extdata) {
                unset($extdata['extid']);
                $extdata['contentid'] = $id;
                $result = Db::name('cms_content_ext')->insert($extdata);
            } else {
                $result = $id;
            }
        }
        return $result;
    }

    // 修改文章
    public function moveContent($id, $scode)
    {
        return $this
            ->where('id', 'in', $id)
            ->update(['scode' => $scode]);
    }

}

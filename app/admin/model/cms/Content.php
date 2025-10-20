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

    // 模型事件
    public static function onBeforeInsert($model)
    {
        $data = $model->getData();
        if (empty($data['description']) && isset($data['content'])) {
            $data['description'] = self::extractDescription($data['content']);
        }

        if (empty($data['ico']) && isset($data['content'])) {
            $data['ico'] = self::extractThumbnail($data['content']);
        }
    }

    public function contentsort()
    {
        return $this->belongsTo(ContentSort::class, 'scode', 'scode');
    }

    public function getIcoAttr($value, $data)
    {
        return $value ? cdnurl($value) : '';
    }

    public function getPicstitleAttr($value, $data)
    {
        if (!$value) {
            return '';
        }
        $value = explode(',', $value);
        if (!$value) {
            return '';
        }

        return json_encode($value);
    }

    public function setPicstitleAttr($value, $data)
    {
        if (!$value) {
            return '';
        }
        // 如果是JSON字符串，先解析为数组
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                return '';
            }
        }
        if (!is_array($value)) {
            return '';
        }
        // 过滤掉空值
        $value = array_filter($value, function ($item) {
            return $item !== '' && $item !== null;
        });
        return implode(',', $value);
    }

    // 检查自定义URL名称
    public function checkFilename($filename, $where = [])
    {
        do {
            $exists = $this->field('id')
                ->where("filename='$filename'")
                ->where($where)
                ->count();
            $filename .= '-' . mt_rand(1, 20);
        } while ($exists);

        return $filename;
    }

    /**
     * 生成一个唯一的aucode
     * @return string
     */
    public function getUniqueAucode(): string
    {
        $aucode = $this->order('aucode', 'desc')->value('aucode');
        do {
            $aucode = (int)$aucode + 1;
            $exists = $this->where('aucode', $aucode)->count();
        } while ($exists);

        return $aucode;
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
            //插入主内容
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

    /**
     * 自动提取描述
     * @param string $content 内容
     * @return string
     */
    public static function extractDescription($content)
    {
        if (!$content) {
            return '';
        }
        return escape_string(clear_html_blank(substr_both(strip_tags($content), 0, 150)));
    }

    /**
     * 自动提取缩略图
     * @param string $content 内容
     * @return string
     */
    public static function extractThumbnail($content)
    {
        if (!$content) {
            return '';
        }

        if (preg_match('/<img\s+.*?src=\s?[\'|\"](.*?(\.gif|\.jpg|\.png|\.jpeg))[\'|\"].*?[\/]?>/i', decode_string($content), $srcs) && isset($srcs[1])) {
            return $srcs[1];
        }

        return '';
    }


}

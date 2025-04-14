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

/**
 * Link 友情链接 模型
 */
class Link extends Model
{
    // 表名
    protected $name = 'cms_link';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    //新增模型自定义字段 分组名称
    protected $append = [
        'gid_text'
    ];

    /**
     * 返回新增gid_text字段返回值
     * @return mixed|string
     */
    public function getGidTextAttr($value, $data)
    {
        return '分组'.$data['gid'];
    }
}

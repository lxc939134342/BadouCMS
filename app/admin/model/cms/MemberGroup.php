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
 * MemberGroup
 */
class MemberGroup extends Model
{
    // 表名
    protected $name = 'cms_member_group';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    /**
     * 获取等级选择列表
     */
    public function getSelect(): array|object
    {
        return $this->where('status', 1)->field('id,gcode,gname')
            ->order('gcode,id')
            ->select();
    }

}

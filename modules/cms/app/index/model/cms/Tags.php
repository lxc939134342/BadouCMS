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

namespace app\index\model\cms;

use think\facade\Db;
use think\Model;

class Tags extends Model
{
    protected $name = "cms_tags";

    /**
     * 获取tags名称
     * @return Tags[]|array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTags()
    {
        $data = $this->field('name,link')
            ->where('acode', '=', get_frontend_lang())
            ->order(Db::raw('length(name) desc'))
            ->select();
        if ($data->isEmpty()) {
            return [];
        }
        return $data->toArray();
    }
}

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

use ba\TableManager;
use think\Model;

class Company extends Model
{
    protected $name = 'cms_company';

    public function getCompanyData()
    {
        $lg = get_frontend_lang();
        $info = $this->where('acode', $lg)->cache('cms_company_'.$lg, 3600 * 24, 'cms_cache')->find();
        $data = [];
        if (!$info) {
            $columns = TableManager::getTableColumns('cms_company', false, 'mysql');
            foreach ($columns as $key => $value) {
                $data['company'.$key] = '';
            }
            return $data;
        }
        $data = $info->toArray();
        foreach ($data as $key => $value) {
            $value = htmlspecialchars_decode_improve($value);
            $data['company'.$key] = $value;
        }
        return $data;
    }
}

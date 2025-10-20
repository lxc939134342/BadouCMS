<?php

namespace app\index\model\cms;

use think\Model;

class Area extends Model
{
    protected $name = 'cms_area';

    public function getList()
    {
        return $this->cache('cms_area')
            ->where('status', 1)
            ->order('is_default DESC')
            ->column('acode,name,domain,is_default', 'acode');
    }
}

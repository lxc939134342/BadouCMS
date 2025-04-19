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

use think\Model;

class Label extends Model
{
    protected $name = 'cms_label';

    public function getLabelData(): array
    {
        $info = $this->cache('cms_label', 3600 * 24, 'cms_cache')->column('value', 'name');
        foreach ($info as $key => &$value) {
            if (!$value) {
                continue;
            }
            $value = htmlspecialchars_decode_improve($value);
        }
        return $info;
    }
}

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

use app\admin\model\cms\Label as adminLabel;
class Label extends adminLabel
{
    protected $name = 'cms_label';

    public function getLabelData(): array
    {
        $acode = get_frontend_lang();
        $info = $this->cache('cms_label_' . $acode, 3600 * 24, 'cms_cache')
            ->where('acode', $acode)
            ->column('value,type', 'name');
        $typeMap=$this->typeList();
        $result=[];
        foreach ($info as $key => $item) {
            if (!$item) {
                continue;
            }
            if($typeMap[$item['type']]['inputType']=='images'){
                $item['value']=explode(',',$item['value']);
            }
            elseif($typeMap[$item['type']]['inputType']=='images_title'){
                $item['value']=json_decode($item['value'],true);
            }else{
                $item['value'] = htmlspecialchars_decode_improve($item['value']);
            }
            $result[$item['name']]=$item['value'];
        }

        return $result;
    }
}

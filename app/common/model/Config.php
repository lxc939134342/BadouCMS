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

namespace app\common\model;

use Throwable;
use think\Model;
use app\admin\model\Config as adminConfigModel;

class Config extends Model
{
    protected $name = 'config';


    /**
     * 本地上传配置信息
     * @return array
     */
    public static function upload()
    {
        $uploadcfg = config('upload');

        $uploadurl = app('http')->getName() ? $uploadcfg['uploadurl'] : ($uploadcfg['uploadurl'] === 'ajax/upload' ? 'index/' . $uploadcfg['uploadurl'] : $uploadcfg['uploadurl']);

        if (!preg_match("/^((?:[a-z]+:)?\/\/)(.*)/i", $uploadurl) && substr($uploadurl, 0, 1) !== '/') {
            $uploadurl = (string) url($uploadurl, [], false);
        }
        $uploadcfg['fullmode'] = isset($uploadcfg['fullmode']) && $uploadcfg['fullmode'];
        $uploadcfg['thumbstyle'] = $uploadcfg['thumbstyle'] ?? '';

        $upload = [
            'cdnurl'     => $uploadcfg['cdnurl'],
            'uploadurl'  => $uploadurl,
            'bucket'     => 'local',
            'maxsize'    => $uploadcfg['maxsize'],
            'mimetype'   => $uploadcfg['mimetype'],
            'chunking'   => $uploadcfg['chunking'],
            'chunksize'  => $uploadcfg['chunksize'],
            'savekey'    => $uploadcfg['savekey'],
            'multipart'  => [],
            'multiple'   => $uploadcfg['multiple'],
            'fullmode'   => $uploadcfg['fullmode'],
            'thumbstyle' => $uploadcfg['thumbstyle'],
            'storage'    => 'local'
        ];
        return $upload;
    }
}

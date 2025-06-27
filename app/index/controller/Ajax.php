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

namespace app\index\controller;

use think\facade\Env;
use think\facade\Lang;
use think\facade\Config;
use app\common\controller\Frontend;

class Ajax extends Frontend
{
    protected $noNeedLogin = ['lang'];

    /**
     * 加载语言包
     */
    public function lang()
    {
        $this->request->get(['callback' => 'lang']);
        $header = ['Content-Type' => 'application/javascript'];
        if (!Env::get('app_debug')) {
            $offset = 30 * 60 * 60 * 24; // 缓存一个月
            $header['Cache-Control'] = 'public';
            $header['Pragma'] = 'cache';
            $header['Expires'] = gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        }
        $lang = $this->request->get('lang');
        $controllername = $this->request->get('controllername');
        if (!$lang || !in_array($lang, config('lang.allow_lang_list')) || !$controllername || !preg_match("/^[a-z0-9_\.]+$/i", $controllername)) {
            return jsonp(['errmsg' => '参数错误'], 200, [], ['json_encode_param' => JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE]);
        }
        $controllername = input("controllername");
        $this->loadlang($controllername, $lang);
        //强制输出JSON Object
        return jsonp(Lang::get(), 200, $header, ['json_encode_param' => JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE]);
    }
}

<?php

namespace app\index\controller;

use think\facade\Env;
use think\facade\Lang;
use think\facade\Config;
use app\common\controller\Frontend;

class Ajax extends Frontend
{
    protected array $noNeedLogin = ['lang'];

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

    /**
     * 加载语言文件
     * @param string $name
     */
    protected function loadlang($name, $lang)
    {
        $name = preg_match("/^([a-zA-Z0-9_\.\/]+)\$/i", $name) ? $name : 'index';

        $lang = preg_match("/^([a-zA-Z\-_]{2,10})\$/i", $lang) ? $lang : 'zh-cn';
        Lang::load(app_path() . 'lang/' . $lang . '/cms/index.php');
        Lang::load(app_path() . 'lang/' . $lang . '/' . str_replace('.', '/', $name) . '.php');
    }
}

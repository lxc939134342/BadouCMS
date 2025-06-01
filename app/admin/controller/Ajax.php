<?php

namespace app\admin\controller;

use think\facade\Db;
use badou\Filesystem;
use think\facade\Env;
use think\facade\Cache;
use think\facade\Event;
use app\common\controller\Backend;
use app\common\library\Upload;
use app\common\exception\UploadException;

class Ajax extends Backend
{
    protected $noNeedLogin = ['lang'];
    protected $noNeedRight = ['*'];

    public function lang()
    {
        $this->request->get(['callback' => 'define']);
        $lang = $this->app->lang->getLangSet();
        $lang = preg_match("/^([a-zA-Z\-_]{2,10})\$/i", $lang) ? $lang : 'zh-cn';

        $header = ['Content-Type' => 'application/javascript'];
        if (!config('app_debug')) {
            $offset = 30 * 60 * 60 * 24; // 缓存一个月
            $header['Cache-Control'] = 'public';
            $header['Pragma'] = 'cache';
            $header['Expires'] = gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        }

        $controllername = input("controllername");
        //默认只加载了控制器对应的语言名，你还根据控制器名来加载额外的语言包
        $arr = $this->loadlang($controllername, $lang);
        return jsonp($arr, 200, $header, ['json_encode_param' => JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE]);
    }

    //缓存更新
    public function wipecache()
    {
        $runtimePath = app()->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR;
        try {
            $type = $this->request->request("type");
            switch ($type) {
                case 'all':
                case 'data':
                    Filesystem::delDir($runtimePath . 'cache');
                    Cache::clear();
                    if ($type == 'data') {
                        break;
                    }
                    // no break
                case 'template':
                    // 模板缓存
                    Filesystem::delDir($runtimePath . 'admin' . DIRECTORY_SEPARATOR . 'temp'); //后台
                    Filesystem::delDir($runtimePath . 'index' . DIRECTORY_SEPARATOR . 'temp'); //前台
                    Filesystem::delDir($runtimePath . 'temp');
                    if ($type == 'template') {
                        break;
                    }
                    //                case 'addons':
                    //                    // 插件缓存
                    //                    Service::refresh();
                    //                    if ($type == 'addons') {
                    //                        break;
                    //                    }
                    // no break
                case 'browser':
                    // 浏览器缓存
                    // 只有生产环境下才修改
                    if (!Env::get('APP_DEBUG')) {
                        $version    = config('site.version');
                        $newversion = preg_replace_callback("/(.*)\.([0-9])\$/", function ($match) {
                            return $match[1] . '.' . ($match[2] + 1);
                        }, $version);
                        if ($newversion && $newversion != $version) {
                            Db::startTrans();
                            try {
                                \app\common\model\Config::where('name', 'version')->update(['value' => $newversion]);
                                \app\common\model\Config::refreshFile();
                                Db::commit();
                            } catch (\Exception $e) {
                                Db::rollback();
                                throw new \Exception($e->getMessage());
                            }
                        }
                    }
                    if ($type == 'browser') {
                        break;
                    }
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        Event::trigger("wipecache_after");
        $this->success('清理缓存成功！');
    }

    public function upload()
    {
        $upload = \app\common\model\Config::upload();

        $attachment = null;
        //默认普通上传文件
        $file = $this->request->file('file');
        try {
            $upload = new Upload($file);
            $attachment = $upload->upload();
        } catch (UploadException $e) {
            $this->error($e->getMessage());
        }

        $this->success(__('Uploaded successful'), '', ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);
    }
}

<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use Throwable;
use app\common\library\Upload;
use app\common\model\Attachment;
use think\response\Json;

class Ueditor extends Backend
{
    protected array $noNeedPermission = ['init'];
    protected $config = [
        // 上传图片配置项
        "imageActionName"         => "image",
        "imageFieldName"          => "file",
        "imageMaxSize"            => 1024 * 1024 * 10,
        "imageAllowFiles"         => ['.jpg', '.png', '.jpeg'],
        "imageCompressEnable"     => true,
        "imageCompressBorder"     => 5000,
        "imageInsertAlign"        => "none",
        "imageUrlPrefix"          => "",

        // 涂鸦图片上传配置项
        "scrawlActionName"        => "crawl",
        "scrawlFieldName"         => "file",
        "scrawlMaxSize"           => 1024 * 1024 * 10,
        "scrawlUrlPrefix"         => "",
        "scrawlInsertAlign"       => "none",

        // 截图工具上传
        "snapscreenActionName"    => "snap",
        "snapscreenUrlPrefix"     => "",
        "snapscreenInsertAlign"   => "none",

        // 抓取
        "catcherLocalDomain"      => [
            "127.0.0.1",
            "localhost",
        ],
        "catcherActionName"       => "catch",
        "catcherFieldName"        => "source",
        "catcherUrlPrefix"        => "",
        "catcherMaxSize"          => 1024 * 1024 * 10,
        "catcherAllowFiles"       => ['.jpg', '.png', '.jpeg'],

        // 上传视频配置
        "videoActionName"         => "video",
        "videoFieldName"          => "file",
        "videoUrlPrefix"          => "",
        "videoMaxSize"            => 1024 * 1024 * 100,
        "videoAllowFiles"         => ['.mp4'],

        // 上传音频配置
        "audioActionName"         => "audio",
        "audioFieldName"          => "file",
        "audioUrlPrefix"          => "",
        "audioMaxSize"            => 1024 * 1024 * 100,
        "audioAllowFiles"         => ['.mp3'],

        // 上传文件配置
        "fileActionName"          => "file",
        "fileFieldName"           => "file",
        "fileUrlPrefix"           => "",
        "fileMaxSize"             => 1024 * 1024 * 100,
        "fileAllowFiles"          => ['.zip', '.pdf', '.doc'],

        // 列出图片
        "imageManagerActionName"  => "listImage",
        "imageManagerListSize"    => 20,
        "imageManagerUrlPrefix"   => "",
        "imageManagerInsertAlign" => "none",
        "imageManagerAllowFiles"  => ['.jpg', '.png', '.jpeg'],

        // 列出指定目录下的文件
        "fileManagerActionName"   => "listFile",
        "fileManagerUrlPrefix"    => "",
        "fileManagerListSize"     => 20,
        "fileManagerAllowFiles"   => ['.zip', '.pdf', '.doc'],

    ];

    public function initialize(): void
    {
        parent::initialize();

        // 允许跨域访问
        $origin = $this->request->header('Origin');
        if ($origin) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With, ba-token');
        }

        // 处理 OPTIONS 请求
        if ($this->request->method(true) == 'OPTIONS') {
            exit();
        }
    }

    public function init()
    {
        $action = $this->request->get('action', 'config');
        $iscallback = $this->request->get('callback', false);
        switch ($action) {
            case 'config':
                if ($iscallback) {
                    return jsonp($this->config);
                }
                return json($this->config);
            case 'showPost':
                return json($this->request->post());
            case 'image':
            case 'video':
            case 'audio':
            case 'file':
            case 'crawl':
                return $this->upload();
            case 'listImage':
            case 'listFile':
                $start = $this->request->get('start/d', 0);
                $size = $this->request->get('size/d', 20);
                $where = [];
                $imageMimetypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                if ($action == 'listImage') {
                    $where[] = ['mimetype', 'in', $imageMimetypes];
                } else {
                    $where[] = ['mimetype', 'not in', $imageMimetypes];
                }

                $model = new Attachment();
                $count = $model
                    ->where($where)
                    ->count();

                $list = $model
                    ->where($where)
                    ->order('id desc')
                    ->limit($start, $size)
                    ->select();

                foreach ($list as $k => &$v) {
                    $v['url'] = full_url($v['url']);
                    $v['mtime'] = $v['create_time'];
                }
                unset($v);

                if ($iscallback) {
                    return jsonp([
                        "state" => "SUCCESS",
                        "list" => $list,
                        "start" => $start,
                        "total" => $count
                    ]);
                }
                return json([
                    "state" => "SUCCESS",
                    "list" => $list,
                    "start" => $start,
                    "total" => $count
                ]);
            case 'catch':
                return json(['state' => 'SUCCESS', 'list' => []]);
            default:
                return json(['state' => 'INVALID_ACTION']);
        }
    }

    protected function upload()
    {
        $file = $this->request->file('file');
        $driver = $this->request->param('driver', 'local');
        $topic = $this->request->param('topic', 'default');

        try {
            $upload = new Upload();
            $attachment = $upload
                ->setFile($file)
                ->setDriver($driver)
                ->setTopic($topic)
                ->upload(null, 0, $this->auth->id);
            unset($attachment['create_time'], $attachment['quote']);

            $result = [
                'state' => 'SUCCESS',
                'url' => full_url($attachment['url']),
            ];
        } catch (Throwable $e) {
            $result = ['state' => $e->getMessage()];
        }
        return json($result);
    }

}

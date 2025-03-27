<?php

namespace app\admin\controller;

use think\facade\Db;
use app\common\controller\Backend;

class Dashboard extends Backend
{
    public function initialize(): void
    {
        parent::initialize();
    }

    public function index(): void
    {
        $server = [
            'php_os'              => PHP_OS,
            'server_name'         => $_SERVER['SERVER_NAME'],
            'server_port'         => $_SERVER['SERVER_PORT'],
            'server_addr'         => isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : $_SERVER['SERVER_ADDR'] ?? '未知',
            'web_software'        => $_SERVER['SERVER_SOFTWARE'] ?? '未知',
            'php_version'         => phpversion(),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size'       => ini_get('post_max_size'),
        ];

        $modelCount = Db::name('cms_model')->where('status', 1)->count();
        $categoryCount = Db::name('cms_content_sort')->where('acode', get_backend_lang())->where('status', 1)->count();
        $counts = [
            'modelCount' => $modelCount,
            'categoryCount' => $categoryCount,
        ];

        $modelList = Db::name('cms_model')->where('status', 1)->select();
        foreach ($modelList as $model) {
            $counts['contentCount'][$model['mcode']] = Db::name('cms_content')->alias('a')->field('count(*) as count')
                ->where('b.mcode', $model['mcode'])
                ->where('a.acode', get_backend_lang())
                ->join('cms_content_sort b', 'a.scode=b.scode', 'LEFT')
                ->join('cms_model d', 'b.mcode=d.mcode', 'LEFT')
                ->find();
        }

        $this->success('', [
            'remark' => get_route_remark(),
            'server' => $server,
            'counts' => $counts,
            'modelList' => $modelList,
        ]);
    }
}

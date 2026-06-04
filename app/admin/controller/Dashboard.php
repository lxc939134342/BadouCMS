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

namespace app\admin\controller;

use app\admin\model\AdminRule;
use app\common\library\Menu;
use badou\Auth;
use badou\Date;
use badou\Server;
use think\facade\Db;
use app\admin\model\Admin;
use app\common\model\User;
use app\common\model\Attachment;
use app\common\controller\Backend;
use app\common\library\AdminAuth;

class Dashboard extends Backend
{
    public function index()
    {
        if ($this->isAjax()) {
        }

        try {
            Db::execute("SET @@sql_mode='';");
        } catch (\Exception $e) {

        }
        $column = [];
        $starttime = Date::unixtime('day', -6);
        $endtime = Date::unixtime('day', 0, 'end');
        $joinlist = Db::name("user")->where('jointime', 'between time', [$starttime, $endtime])
            ->field('jointime, status, COUNT(*) AS nums, DATE_FORMAT(FROM_UNIXTIME(jointime), "%Y-%m-%d") AS join_date')
            ->group('join_date')
            ->select();
        for ($time = $starttime; $time <= $endtime;) {
            $column[] = date("Y-m-d", $time);
            $time += 86400;
        }
        $userlist = array_fill_keys($column, 0);
        foreach ($joinlist as $k => $v) {
            $userlist[$v['join_date']] = $v['nums'];
        }

        $dbTableList = Db::query("SHOW TABLE STATUS");
        $installedModules = Server::getInstalldModuleList();
        $totalmodule = count($installedModules);
        $quickmenu = $this->auth->getOriginAuthRules($this->auth->id);
        $quickmenu = array_values(array_filter($quickmenu, static function ($item) {
            return (int)($item['ismenu'] ?? 0) === 1
                && (int)($item['type'] ?? -1) === AdminRule::RULE_URL
                && (int)($item['is_quick'] ?? 0) === 1
                && !empty($item['href']);
        }));

        $this->view->assign([
            'totaluser'         => User::count(),
            'totalmodule'        => $totalmodule,
            'totaladmin'        => Admin::count(),
            'dbtablenums'       => count($dbTableList),
            'dbsize'            => array_sum(array_map(function ($item) {
                return $item['Data_length'] + $item['Index_length'];
            }, $dbTableList)),
            'attachmentnums'      => Attachment::count(),
            'attachmentsize'      => Attachment::sum('filesize'),
            'picturenums'         => Attachment::where('mimetype', 'like', 'image/%')->count(),
            'picturesize'         => Attachment::where('mimetype', 'like', 'image/%')->sum('filesize'),
            'php_os'              => PHP_OS,
            'server_name'         => $_SERVER['SERVER_NAME'],
            'server_port'         => $_SERVER['SERVER_PORT'],
            'server_addr'         => isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : $_SERVER['SERVER_ADDR'] ?? '未知',
            'web_software'        => $_SERVER['SERVER_SOFTWARE'] ?? '未知',
            'php_version'         => phpversion(),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size'       => ini_get('post_max_size'),
            'quickmenu'           => $quickmenu
        ]);


        $this->assignconfig('column', array_keys($userlist));
        $this->assignconfig('userdata', array_values($userlist));
        return $this->view->fetch();
    }
}

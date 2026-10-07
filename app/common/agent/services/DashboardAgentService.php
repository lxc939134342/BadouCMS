<?php

namespace app\common\agent\services;

use app\admin\model\Admin;
use app\common\model\Attachment;
use app\common\model\User;
use badou\Server;
use think\facade\Db;

class DashboardAgentService
{
    public function summary(): array
    {
        $tables = Db::query('SHOW TABLE STATUS');
        $dbSize = 0;
        foreach ($tables as $table) {
            $dbSize += (int)($table['Data_length'] ?? 0) + (int)($table['Index_length'] ?? 0);
        }
        return [
            'users' => (int)User::count(),
            'admins' => (int)Admin::count(),
            'modules' => count(Server::getInstalldModuleList()),
            'tables' => count($tables),
            'database_size' => format_bytes($dbSize),
            'attachments' => (int)Attachment::count(),
            'attachment_size' => format_bytes((int)Attachment::sum('filesize')),
            'pictures' => (int)Attachment::where('mimetype', 'like', 'image/%')->count(),
        ];
    }
}

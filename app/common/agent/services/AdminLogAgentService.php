<?php

namespace app\common\agent\services;

use app\admin\model\Adminlog;
use app\common\library\AdminAuth;

class AdminLogAgentService
{
    public function search(AdminAuth $auth, string $keyword, int $limit = 20): array
    {
        $limit = max(1, min(50, $limit));
        $keyword = trim($keyword);
        $query = Adminlog::field('id,admin_id,username,title,url,ip,create_time');
        if (!$auth->isSuperAdmin()) {
            $ids = array_map('intval', $auth->getChildrenAdminIds(true));
            if ($ids === []) {
                return ['total' => 0, 'logs' => []];
            }
            $query->where('admin_id', 'in', $ids);
        }
        if ($keyword !== '') {
            $query->where(function ($query) use ($keyword): void {
                $query->where('username', 'like', '%' . $keyword . '%')
                    ->whereOr('title', 'like', '%' . $keyword . '%')
                    ->whereOr('url', 'like', '%' . $keyword . '%');
            });
        }
        $logs = $query->order('id', 'desc')->limit($limit)->select()->toArray();
        foreach ($logs as &$log) {
            $log['url'] = mb_substr((string)$log['url'], 0, 180);
        }
        unset($log);
        return ['total' => count($logs), 'logs' => $logs];
    }
}

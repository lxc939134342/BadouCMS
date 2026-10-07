<?php

namespace app\common\agent\services;

use app\admin\model\AdminGroup;
use app\admin\model\AdminGroupAccess;
use app\admin\model\AdminRule;
use app\common\library\AdminAuth;

class AdminGroupAgentService
{
    public function search(AdminAuth $auth, string $keyword, int $limit = 20): array
    {
        $ids = array_map('intval', $auth->getChildrenGroupIds(true));
        if ($ids === []) {
            return ['total' => 0, 'groups' => []];
        }
        $keyword = trim($keyword);
        $limit = max(1, min(50, $limit));
        $query = AdminGroup::field('id,pid,name,status')->where('id', 'in', $ids);
        if ($keyword !== '') {
            $query->where(function ($query) use ($keyword): void {
                if (ctype_digit($keyword)) {
                    $query->where('id', (int)$keyword);
                }
                $query->whereOr('name', 'like', '%' . $keyword . '%');
            });
        }
        $groups = $query->order('id', 'asc')->limit($limit)->select()->toArray();
        foreach ($groups as &$group) {
            unset($group['rules']);
        }
        unset($group);
        return ['total' => count($groups), 'groups' => $groups];
    }

    public function rules(AdminAuth $auth, int $groupId, int $limit = 50): array
    {
        $ids = array_map('intval', $auth->getChildrenGroupIds(true));
        if (!in_array($groupId, $ids, true)) {
            throw new \DomainException('没有权限查看该角色组');
        }
        $group = AdminGroup::field('id,name,rules,status')->find($groupId);
        if (!$group) {
            throw new \DomainException('角色组不存在');
        }
        $rules = trim((string)$group->getData('rules'));
        if ($rules === '*') {
            return [
                'group' => ['id' => $groupId, 'name' => $group->getAttr('name'), 'status' => $group->getAttr('status')],
                'all' => true,
                'rules' => [],
            ];
        }
        $ruleIds = array_values(array_filter(array_map('intval', explode(',', $rules))));
        $limit = max(1, min(100, $limit));
        $rows = $ruleIds === [] ? [] : AdminRule::field('id,pid,name,title,ismenu,status')
            ->where('id', 'in', $ruleIds)
            ->order('weigh', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
        return [
            'group' => ['id' => $groupId, 'name' => $group->getAttr('name'), 'status' => $group->getAttr('status')],
            'all' => false,
            'total' => count($ruleIds),
            'returned' => count($rows),
            'rules' => $rows,
        ];
    }

    public function members(AdminAuth $auth, int $groupId, int $limit = 20): array
    {
        $ids = array_map('intval', $auth->getChildrenGroupIds(true));
        if (!in_array($groupId, $ids, true)) {
            throw new \DomainException('没有权限查看该角色组');
        }
        $limit = max(1, min(50, $limit));
        $uids = AdminGroupAccess::where('group_id', $groupId)->limit($limit)->column('uid');
        $admins = $uids === [] ? [] : \app\admin\model\Admin::field('id,username,nickname,status')
            ->where('id', 'in', $uids)
            ->select()
            ->toArray();
        return ['group_id' => $groupId, 'admins' => $admins];
    }
}

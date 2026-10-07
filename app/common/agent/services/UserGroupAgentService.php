<?php

namespace app\common\agent\services;

use app\admin\model\User;
use app\admin\model\UserGroup;

class UserGroupAgentService
{
    public function search(string $keyword, int $limit = 20): array
    {
        $keyword = trim($keyword);
        $limit = max(1, min(50, $limit));
        $query = UserGroup::field('id,name,status');
        if ($keyword !== '') {
            $query->where(function ($query) use ($keyword): void {
                if (ctype_digit($keyword)) {
                    $query->where('id', (int)$keyword);
                }
                $query->whereOr('name', 'like', '%' . $keyword . '%');
            });
        }
        $groups = $query->order('id', 'asc')->limit($limit)->select()->toArray();
        return ['total' => count($groups), 'groups' => $groups];
    }

    public function updateField(int $id, string $field, string $value): array
    {
        $field = trim($field);
        if (!in_array($field, ['name', 'status'], true)) {
            throw new \DomainException('该会员角色组字段不能通过 Agent 修改');
        }
        $group = UserGroup::find($id);
        if (!$group) {
            throw new \DomainException('会员角色组不存在');
        }
        $value = trim($value);
        if ($field === 'name' && ($value === '' || mb_strlen($value) > 50)) {
            throw new \DomainException('角色组名称无效');
        }
        if ($field === 'status' && !in_array($value, ['normal', 'hidden'], true)) {
            throw new \DomainException('角色组状态无效');
        }
        if ($group->save([$field => $value]) === false) {
            throw new \RuntimeException('会员角色组保存失败');
        }
        return ['id' => $id, 'field' => $field, 'value' => $group->getAttr($field)];
    }

    public function members(int $id, int $limit = 20): array
    {
        if (!UserGroup::find($id)) {
            throw new \DomainException('会员角色组不存在');
        }
        $limit = max(1, min(20, $limit));
        $users = User::field('id,username,nickname,status')
            ->where('group_id', $id)
            ->order('id', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
        return ['group_id' => $id, 'users' => $users];
    }
}

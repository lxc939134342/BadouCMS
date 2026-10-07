<?php

namespace app\common\agent\services;

use app\admin\model\Admin;
use app\admin\model\AdminGroupAccess;
use app\admin\validate\AdminUser as AdminUserValidator;
use app\common\library\AdminAuth;
use app\common\model\Attachment;

class AdminAgentService
{
    public function search(AdminAuth $auth, string $keyword, int $limit = 20): array
    {
        $keyword = trim($keyword);
        $limit = max(1, min(20, $limit));
        $ids = $this->visibleAdminIds($auth);
        if ($ids === []) {
            return ['total' => 0, 'admins' => []];
        }

        $query = Admin::field('id,username,nickname,email,mobile,status')->where('id', 'in', $ids);
        if ($keyword !== '') {
            $query->where(function ($query) use ($keyword): void {
                if (ctype_digit($keyword)) {
                    $query->where('id', (int)$keyword);
                }
                $query->whereOr('username', 'like', '%' . $keyword . '%')
                    ->whereOr('nickname', 'like', '%' . $keyword . '%');
            });
        }

        $rows = $query->order('id', 'desc')->limit($limit)->select()->toArray();
        $groupNames = $this->groupNames(array_column($rows, 'id'));
        foreach ($rows as &$row) {
            $row['groups'] = $groupNames[(int)$row['id']] ?? [];
        }
        unset($row);

        return ['total' => count($rows), 'admins' => $rows];
    }

    public function updateField(AdminAuth $auth, int $id, string $field, string $value, int $adminId): array
    {
        $field = trim($field);
        $allowed = ['nickname', 'email', 'mobile', 'status', 'avatar'];
        if (!in_array($field, $allowed, true)) {
            throw new \DomainException('该管理员字段不能通过 Agent 修改');
        }
        if (!in_array($id, $this->visibleAdminIds($auth, true), true)) {
            throw new \DomainException('没有权限操作该管理员');
        }

        $admin = Admin::find($id);
        if (!$admin) {
            throw new \DomainException('管理员不存在');
        }

        $value = trim($value);
        if (in_array($field, ['email', 'mobile'], true) && $value !== '') {
            (new AdminUserValidator())
                ->only([$field])
                ->failException(true)
                ->check(['id' => $id, $field => $value]);
        }
        if ($field === 'status' && !in_array($value, ['normal', 'hidden'], true)) {
            throw new \DomainException('管理员状态无效');
        }
        if ($field === 'nickname' && ($value === '' || mb_strlen($value) > 50)) {
            throw new \DomainException('昵称无效');
        }
        if ($field === 'avatar' && $value !== '' && !Attachment::where('url', $value)
            ->where('admin_id', $adminId)
            ->where('mimetype', 'like', 'image/%')
            ->find()) {
            throw new \DomainException('头像只能使用当前管理员已上传的图片附件');
        }

        if ($admin->save([$field => $value]) === false) {
            throw new \RuntimeException('管理员资料保存失败');
        }
        return ['id' => $id, 'field' => $field, 'value' => $admin->getAttr($field)];
    }

    public function resetPasswordPreview(AdminAuth $auth, int $id): array
    {
        return [
            'requires_confirmation' => true,
            'reset' => false,
            'message' => '尚未重置密码。请向用户确认后，再以 confirm=true 调用 admin_reset_password。',
            'admin' => $this->snapshot($auth, $id),
        ];
    }

    public function resetPassword(AdminAuth $auth, int $id, string $password): array
    {
        $password = trim($password);
        if (strlen($password) < 3 || strlen($password) > 20) {
            throw new \DomainException('密码长度需要 3 到 20 位');
        }
        if (!in_array($id, $this->visibleAdminIds($auth, true), true)) {
            throw new \DomainException('没有权限操作该管理员');
        }
        $admin = Admin::find($id);
        if (!$admin) {
            throw new \DomainException('管理员不存在');
        }
        $admin->save([
            'password' => (new AdminAuth())->getEncryptPassword($password),
            'token' => '',
        ]);
        return ['reset' => true, 'admin' => $this->snapshot($auth, $id)];
    }

    public function deletePreview(AdminAuth $auth, int $id): array
    {
        return [
            'requires_confirmation' => true,
            'deleted' => false,
            'message' => '尚未删除。请向用户确认后，再以 confirm=true 调用 admin_delete。',
            'admin' => $this->snapshot($auth, $id),
        ];
    }

    public function delete(AdminAuth $auth, int $id): array
    {
        if ($id === (int)$auth->id) {
            throw new \DomainException('不能删除当前登录管理员');
        }
        $ids = array_intersect([$id], $this->visibleAdminIds($auth));
        if ($ids === []) {
            throw new \DomainException('没有权限删除该管理员');
        }

        $snapshot = $this->snapshot($auth, $id);
        $admin = Admin::find($id);
        $admin->startTrans();
        try {
            $admin->delete();
            AdminGroupAccess::where('uid', $id)->delete();
            $admin->commit();
        } catch (\Throwable $e) {
            $admin->rollback();
            throw $e;
        }
        return ['deleted' => true, 'admin' => $snapshot];
    }

    /** @return int[] */
    private function visibleAdminIds(AdminAuth $auth, bool $withSelf = false): array
    {
        return array_map('intval', $auth->getChildrenAdminIds($withSelf));
    }

    private function snapshot(AdminAuth $auth, int $id): array
    {
        if (!in_array($id, $this->visibleAdminIds($auth, true), true)) {
            throw new \DomainException('没有权限查看该管理员');
        }
        $admin = Admin::field('id,username,nickname,email,mobile,status')->find($id);
        if (!$admin) {
            throw new \DomainException('管理员不存在');
        }
        $row = $admin->toArray();
        $row['groups'] = $this->groupNames([$id])[$id] ?? [];
        return $row;
    }

    /** @return array<int, string[]> */
    private function groupNames(array $adminIds): array
    {
        $adminIds = array_values(array_filter(array_map('intval', $adminIds)));
        if ($adminIds === []) {
            return [];
        }
        $access = AdminGroupAccess::where('uid', 'in', $adminIds)->field('uid,group_id')->select();
        $groupIds = [];
        foreach ($access as $row) {
            $groupIds[] = (int)$row['group_id'];
        }
        $names = $groupIds === [] ? [] : \app\admin\model\AdminGroup::where('id', 'in', $groupIds)->column('name', 'id');
        $result = [];
        foreach ($access as $row) {
            $name = $names[(int)$row['group_id']] ?? '';
            if ($name !== '') {
                $result[(int)$row['uid']][] = $name;
            }
        }
        return $result;
    }
}

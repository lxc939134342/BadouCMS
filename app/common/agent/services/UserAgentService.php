<?php

namespace app\common\agent\services;

use app\admin\model\User;
use app\admin\model\UserGroup;
use app\admin\validate\User as UserValidator;
use app\common\model\Attachment;

class UserAgentService
{
    public function search(string $keyword, int $beforeId = 0, int $limit = 10, string $sort = 'id', string $order = 'desc', int $page = 1): array
    {
        $keyword = trim($keyword);
        $limit = max(1, min(20, $limit));
        $page = max(1, $page);
        $sort = strtolower(trim($sort));
        $sortable = ['id', 'money', 'score', 'jointime', 'logintime', 'prevtime', 'update_time', 'create_time', 'group_id'];
        if (!in_array($sort, $sortable, true)) {
            $sort = 'id';
        }
        $order = strtolower(trim($order)) === 'asc' ? 'asc' : 'desc';
        // before_id 游标只在 id 倒序下成立；其他排序改用 page 翻页。
        $cursorMode = $sort === 'id' && $order === 'desc';

        $fields = ['id', 'username', 'nickname', 'avatar', 'status'];
        if (!in_array($sort, $fields, true)) {
            $fields[] = $sort;
        }
        $query = User::field(implode(',', $fields));
        if ($keyword !== '') {
            $query->where(function ($query) use ($keyword): void {
                if (ctype_digit($keyword)) {
                    $query->where('id', (int)$keyword)->whereOr('username', 'like', '%' . $keyword . '%')->whereOr('nickname', 'like', '%' . $keyword . '%');
                } else {
                    $query->where('username', 'like', '%' . $keyword . '%')->whereOr('nickname', 'like', '%' . $keyword . '%');
                }
            });
        }

        $total = (clone $query)->count();
        if ($cursorMode) {
            if ($beforeId > 0) {
                $query->where('id', '<', $beforeId);
            }
            $query->limit($limit + 1);
        } else {
            $query->limit(($page - 1) * $limit, $limit + 1);
        }
        $query->order($sort, $order);
        if ($sort !== 'id') {
            $query->order('id', 'desc');
        }
        $rows = $query->select()->toArray();
        $hasMore = count($rows) > $limit;
        $users = array_slice($rows, 0, $limit);

        return [
            'total' => $total,
            'users' => $users,
            'returned' => count($users),
            'has_more' => $hasMore,
            'sort' => $sort,
            'order' => $order,
            'next_before_id' => $cursorMode && $hasMore ? (int)end($users)['id'] : null,
            'page' => $cursorMode ? null : $page,
            'next_page' => !$cursorMode && $hasMore ? $page + 1 : null,
        ];
    }

    public function updateField(int $id, string $field, string $value, int $adminId): array
    {
        $field = trim($field);
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $field)) {
            throw new \DomainException('会员字段名无效');
        }

        // Agent 可使用现有会员模型保存，但不能改写账号凭据和登录记录。
        $protected = [
            'id', 'password', 'token', 'verification', 'create_time', 'update_time',
            'prevtime', 'logintime', 'loginip', 'loginfailure', 'loginfailuretime',
            'joinip', 'jointime',
        ];
        if (in_array($field, $protected, true)) {
            throw new \DomainException('该会员字段不能通过 Agent 修改');
        }

        $columns = (new User())->db()->getFields();
        if (!isset($columns[$field])) {
            throw new \DomainException('会员字段不存在');
        }

        $value = trim($value);
        // 与后台编辑页使用同一个验证器，修改验证规则后两处会一起生效。
        if (in_array($field, ['username', 'nickname', 'email', 'mobile'], true)) {
            (new UserValidator())
                ->only([$field])
                ->failException(true)
                ->check(['id' => $id, $field => $value]);
        }
        if ($field === 'status' && !in_array($value, ['normal', 'hidden'], true)) {
            throw new \DomainException('会员状态无效');
        }
        if ($field === 'gender' && !in_array($value, ['0', '1'], true)) {
            throw new \DomainException('性别值无效');
        }
        if ($field === 'birthday' && $value !== '' && !ctype_digit($value)) {
            $timestamp = strtotime($value);
            if ($timestamp === false) {
                throw new \DomainException('生日格式无效');
            }
            $value = (string)$timestamp;
        }
        if ($field === 'avatar' && $value !== '' && !Attachment::where('url', $value)
            ->where('user_id', $adminId)
            ->whereIn('mimetype', ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->find()) {
            throw new \DomainException('头像只能使用当前管理员已上传的图片附件');
        }

        $type = strtolower((string)($columns[$field]['type'] ?? ''));
        if (preg_match('/^(?:var)?char\((\d+)\)/', $type, $matches) && mb_strlen($value) > (int)$matches[1]) {
            throw new \DomainException('会员字段内容超过最大长度');
        }
        if (preg_match('/^(tinyint|smallint|mediumint|int|bigint)\b/', $type, $matches) && !($field === 'birthday' && $value === '')) {
            $number = filter_var($value, FILTER_VALIDATE_INT);
            $unsigned = str_contains($type, 'unsigned');
            $limits = ['tinyint' => 8, 'smallint' => 16, 'mediumint' => 24, 'int' => 32];
            $bits = $limits[$matches[1]] ?? 64;
            $max = $bits === 64 ? PHP_INT_MAX : 2 ** ($bits - ($unsigned ? 0 : 1)) - 1;
            $min = $unsigned ? 0 : ($bits === 64 ? PHP_INT_MIN : -(2 ** ($bits - 1)));
            if ($number === false || $number < $min || $number > $max) {
                throw new \DomainException('会员字段需要有效整数');
            }
        } elseif (preg_match('/^decimal\((\d+),(\d+)\)/', $type, $matches)) {
            $digits = (int)$matches[1] - (int)$matches[2];
            $decimals = (int)$matches[2];
            if (!preg_match('/^-?\d{1,' . $digits . '}(?:\.\d{1,' . $decimals . '})?$/', $value)) {
                throw new \DomainException('会员字段需要有效金额');
            }
        } elseif (preg_match('/^(?:float|double)\b/', $type) && (!is_numeric($value) || !is_finite((float)$value))) {
            throw new \DomainException('会员字段需要有效数字');
        }
        if ($field === 'group_id' && (int)$value > 0 && !UserGroup::find((int)$value)) {
            throw new \DomainException('会员分组不存在');
        }

        $user = User::find($id);
        if (!$user) {
            throw new \DomainException('会员不存在');
        }
        $user->startTrans();
        try {
            if ($user->save([$field => $field === 'birthday' && $value === '' ? null : $value]) === false) {
                throw new \RuntimeException('会员资料保存失败');
            }
            $user->commit();
        } catch (\Throwable $e) {
            $user->rollback();
            throw $e;
        }
        return ['id' => $id, 'field' => $field, 'value' => $user->getAttr($field)];
    }

    public function deletePreview(int $id): array
    {
        $user = User::field('id,username,nickname,email,mobile,status')->find($id);
        if (!$user) {
            throw new \DomainException('会员不存在');
        }
        return [
            'requires_confirmation' => true,
            'deleted' => false,
            'message' => '尚未删除。请向用户复述该会员信息并确认后，再以 confirm=true 调用 user_delete。',
            'user' => $user->toArray(),
        ];
    }

    public function delete(int $id): array
    {
        $user = User::find($id);
        if (!$user) {
            throw new \DomainException('会员不存在');
        }
        $snapshot = [
            'id' => (int)$user->getData('id'),
            'username' => (string)$user->getData('username'),
            'nickname' => (string)$user->getData('nickname'),
        ];

        $user->startTrans();
        try {
            if ($user->delete() === false) {
                throw new \RuntimeException('会员删除失败');
            }
            $user->commit();
        } catch (\Throwable $e) {
            $user->rollback();
            throw $e;
        }
        return ['deleted' => true, 'user' => $snapshot];
    }
}

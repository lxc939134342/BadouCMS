<?php

namespace app\common\agent\services;

use app\admin\model\UserLevel;
use app\admin\validate\UserLevel as UserLevelValidator;

class UserLevelAgentService
{
    public function search(string $keyword, int $limit = 20): array
    {
        $keyword = trim($keyword);
        $limit = max(1, min(50, $limit));
        $query = UserLevel::field('id,gcode,gname,description,status,lscore,uscore');
        if ($keyword !== '') {
            $query->where(function ($query) use ($keyword): void {
                if (ctype_digit($keyword)) {
                    $query->where('id', (int)$keyword)->whereOr('gcode', (int)$keyword);
                }
                $query->whereOr('gname', 'like', '%' . $keyword . '%');
            });
        }
        $levels = $query->order('gcode', 'asc')->limit($limit)->select()->toArray();
        return ['total' => count($levels), 'levels' => $levels];
    }

    public function updateField(int $id, string $field, string $value): array
    {
        $field = trim($field);
        $allowed = ['gname', 'description', 'status', 'gcode', 'lscore', 'uscore'];
        if (!in_array($field, $allowed, true)) {
            throw new \DomainException('该会员等级字段不能通过 Agent 修改');
        }
        $level = UserLevel::find($id);
        if (!$level) {
            throw new \DomainException('会员等级不存在');
        }
        $value = trim($value);
        if ($field === 'gcode') {
            $code = filter_var($value, FILTER_VALIDATE_INT);
            if ($code === false) {
                throw new \DomainException('等级编号无效');
            }
            (new UserLevelValidator())->only(['gcode'])->failException(true)->check(['id' => $id, 'gcode' => $code]);
            $value = (string)$code;
        }
        if ($field === 'status' && !in_array($value, ['0', '1'], true)) {
            throw new \DomainException('等级状态无效，使用 1 启用或 0 停用');
        }
        if (in_array($field, ['lscore', 'uscore'], true)) {
            $score = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if ($score === false) {
                throw new \DomainException('积分范围无效');
            }
            $value = (string)$score;
        }
        if ($field === 'gname' && ($value === '' || mb_strlen($value) > 100)) {
            throw new \DomainException('等级名称无效');
        }
        if ($field === 'description' && mb_strlen($value) > 200) {
            throw new \DomainException('等级描述超过最大长度');
        }
        if ($level->save([$field => $value]) === false) {
            throw new \RuntimeException('会员等级保存失败');
        }
        return ['id' => $id, 'field' => $field, 'value' => $level->getAttr($field)];
    }
}

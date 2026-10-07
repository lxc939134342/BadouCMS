<?php

namespace app\common\agent\model;

use app\admin\model\{Admin, AdminGroup, AdminGroupAccess, AdminRule};

/** 长任务每批重新读取权限，避免 Auth 的进程内静态缓存保留已撤回权限。 */
class TaskAdmin extends Admin
{
    public function freshRules(): array
    {
        $groupIds = AdminGroupAccess::where('uid', $this->id)->column('group_id');
        $ids = [];
        foreach (AdminGroup::whereIn('id', $groupIds)->where('status', 'normal')->column('rules') as $rules) {
            $ids = array_merge($ids, explode(',', trim($rules, ',')));
        }
        if (in_array('*', $ids, true)) return ['*'];
        if (!$ids) return [];
        $names = [];
        foreach (AdminRule::whereIn('id', array_unique($ids))->where('status', 'normal')->select() as $rule) {
            // 条件表达式沿用后台 Auth 的字段比较语义。
            if (!empty($rule->condition)) {
                $parts = explode("\r\n", preg_replace('/\{(\w*?)\}/', '\\1', str_replace(['&&', '||'], "\r\n", $rule->condition)));
                $matched = 0;
                foreach ($parts as $part) {
                    preg_match('/^(\w+)\s?([\>\<\=]+)\s?(.*)$/', trim($part), $values);
                    if ($values && isset($this[$values[1]]) && version_compare((string)$this[$values[1]], $values[3], $values[2])) $matched++;
                }
                if (!((stripos($rule->condition, '||') !== false && $matched > 0) || count($parts) === $matched)) continue;
            }
            $names[] = strtolower($rule->name);
        }
        return array_unique($names);
    }
}

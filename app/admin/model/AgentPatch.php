<?php

namespace app\admin\model;

use think\Model;

class AgentPatch extends Model
{
    protected $name = 'agent_patch';
    protected $autoWriteTimestamp = 'int';

    // 必须声明为 int：bigint 列不会被自动时间戳识别为整数，否则会写入日期字符串导致截断报错。
    protected $type = [
        'create_time' => 'int',
        'update_time' => 'int',
        'apply_time' => 'int',
    ];

    public static function listForChat(int $chatId, int $tenantId, int $adminId): array
    {
        return static::where('chat_id', $chatId)
            ->where('tenant_id', $tenantId)
            ->where('admin_id', $adminId)
            ->order('id', 'asc')
            ->withoutField('old_string,new_string,backup')
            ->select()
            ->toArray();
    }

    public static function findForUser(int $id, int $tenantId, int $adminId): ?self
    {
        return static::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('admin_id', $adminId)
            ->find();
    }
}

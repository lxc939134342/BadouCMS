<?php

namespace app\common\agent\tools;

use app\common\agent\AgentContext;
use app\common\agent\AbstractAgentToolProvider;
use app\common\agent\services\UserAgentService;

class UserToolProvider extends AbstractAgentToolProvider
{
    public function guidelines(): string
    {
        return 'user_search 返回的 total 是匹配总数，users 只是本次返回的一页；has_more 为 true 时不能说已经列出全部会员，默认排序可用 next_before_id 继续查询。需要按金额、积分、注册时间等排序时传 sort 和 order，此时用 next_page 翻页、不再用 before_id。会员很多时先报告总数并请用户缩小范围，不要把大量会员资料一次写进回答。用户明确要求修改会员资料时，先通过查询确定会员 ID；目标不唯一时询问用户，不要猜测。修改会员时使用 user_update_field，field 填会员字段名，每次只修改一个字段。删除会员必须二次确认：先用 user_search 确定唯一目标并向用户复述该会员信息，使用 user_delete 的 confirm=false 预览，confirm=true 将触发人工审批。用户名是登录名，昵称是展示名。展示会员头像时用 Markdown 图片语法 ![昵称](avatar 地址) 直接渲染图片，avatar 为空时显示“无”，不要用“已设置”之类的文字代替头像。图片由聊天框先上传到附件库；只有拿到附件 URL 后才能修改 avatar。';
    }

    public function agentTools(): array
    {
        $users = new UserAgentService();

        return [
            [
                'name' => 'user_search',
                'label' => '查询会员',
                'description' => '按会员 ID、用户名或昵称查询会员，可按指定字段排序。默认按 id 倒序，用 before_id 游标翻页（返回 next_before_id）；使用其他排序字段时改用 page 翻页（返回 next_page）。返回匹配总数 total 和最多 20 条本页资料。修改前先确定目标会员 ID。',
                'permission' => 'user.user/index',
                'properties' => [
                    ['name' => 'keyword', 'type' => 'string', 'description' => '会员 ID、用户名或昵称；空字符串表示最近的会员', 'required' => true],
                    ['name' => 'sort', 'type' => 'string', 'description' => '排序字段，默认 id', 'enum' => ['id', 'money', 'score', 'jointime', 'logintime', 'prevtime', 'update_time', 'create_time', 'group_id']],
                    ['name' => 'order', 'type' => 'string', 'description' => '排序方向，desc 降序（默认）或 asc 升序', 'enum' => ['desc', 'asc']],
                    ['name' => 'before_id', 'type' => 'integer', 'description' => 'id 倒序时的下一页游标；仅默认排序时使用，首次省略'],
                    ['name' => 'page', 'type' => 'integer', 'description' => '使用其他排序字段时的页码，从 1 开始，默认 1'],
                    ['name' => 'limit', 'type' => 'integer', 'description' => '每次返回数量，默认 10，最多 20'],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($users): array {
                    if (!is_string($input['keyword'] ?? null)) {
                        throw new \DomainException('查询词格式无效');
                    }
                    $beforeId = filter_var($input['before_id'] ?? 0, FILTER_VALIDATE_INT);
                    $limit = filter_var($input['limit'] ?? 10, FILTER_VALIDATE_INT);
                    $page = filter_var($input['page'] ?? 1, FILTER_VALIDATE_INT);
                    if ($beforeId === false || $beforeId < 0 || $limit === false || $limit < 1 || $page === false || $page < 1) {
                        throw new \DomainException('分页参数无效');
                    }
                    $sort = is_string($input['sort'] ?? null) ? $input['sort'] : 'id';
                    $order = is_string($input['order'] ?? null) ? $input['order'] : 'desc';
                    return $users->search($input['keyword'], $beforeId, $limit, $sort, $order, $page);
                },
            ],
            [
                'name' => 'user_update_field',
                'label' => '修改会员资料',
                'description' => '按会员 ID 修改一个会员资料字段。field 使用数据库字段名；支持后台可编辑的资料字段，包括 username、nickname、email、mobile、avatar、group_id、status、level、gender、birthday、bio、money、score。头像必须使用当前管理员上传的附件 URL；目标不明确时先查询。',
                'permission' => 'user.user/edit',
                'properties' => [
                    ['name' => 'user_id', 'type' => 'integer', 'description' => '要修改的会员 ID', 'required' => true],
                    ['name' => 'field', 'type' => 'string', 'description' => '要修改的会员字段名', 'required' => true],
                    ['name' => 'value', 'type' => 'string', 'description' => '新的字段值；数字也作为字符串传入', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($users): array {
                    $id = filter_var($input['user_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($id === false || !is_string($input['field'] ?? null) || !is_string($input['value'] ?? null)) {
                        throw new \DomainException('会员 ID 或资料参数无效');
                    }
                    return ['user' => $users->updateField($id, $input['field'], $input['value'], $context->adminId)];
                },
            ],
            [
                'name' => 'user_delete',
                'approval' => 'confirm',
                'label' => '删除会员',
                'description' => '删除指定会员。先用 user_search 核对唯一目标；confirm=false 只预览，confirm=true 触发人工审批，用户在界面批准后才删除。',
                'permission' => 'user.user/del',
                'properties' => [
                    ['name' => 'user_id', 'type' => 'integer', 'description' => '要删除的会员 ID', 'required' => true],
                    ['name' => 'confirm', 'type' => 'boolean', 'description' => 'false 只预览；true 请求人工审批，批准后才执行', 'required' => true],
                ],
                'handler' => static function (AgentContext $context, array $input) use ($users): array {
                    $id = filter_var($input['user_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($id === false) {
                        throw new \DomainException('会员 ID 无效');
                    }
                    if (filter_var($input['confirm'] ?? false, FILTER_VALIDATE_BOOLEAN) !== true) {
                        return $users->deletePreview($id);
                    }
                    return $users->delete($id);
                },
            ],
        ];
    }
}

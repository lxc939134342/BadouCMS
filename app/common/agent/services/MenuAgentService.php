<?php

namespace app\common\agent\services;

use app\admin\model\AdminRule;

class MenuAgentService
{
    /** 允许 Agent 修改的菜单规则字段。 */
    private const EDITABLE = [
        'name', 'title', 'url', 'icon', 'condition', 'remark', 'extend',
        'menutype', 'type', 'ismenu', 'is_quick', 'status', 'weigh', 'pid',
    ];

    /** 允许批量修改的字段。 */
    private const BATCH_FIELDS = ['weigh', 'status', 'ismenu', 'is_quick'];

    /**
     * 把当前账号可见的菜单树整理成可跳转列表。
     * $tree 由 $context->auth->getMenus($adminId) 提供，默认只看一级菜单。
     */
    public function navigation(array $tree, string $keyword = '', int $level = 1, int $limit = 10, bool $canManage = false): array
    {
        $keyword = trim($keyword);
        $level = $level < 0 ? 1 : $level;
        $limit = max(1, min(50, $limit));
        // 有关键词时跨层级搜索，方便定位具体功能。
        $effectiveLevel = $keyword !== '' ? 0 : $level;

        $menus = [];
        $this->flattenMenus($tree, $menus, []);

        $matched = [];
        foreach ($menus as $menu) {
            $menuLevel = count($menu['path']) ?: 1;
            if ($effectiveLevel > 0 && $menuLevel !== $effectiveLevel) {
                continue;
            }
            if ($keyword !== ''
                && mb_stripos($menu['title'], $keyword) === false
                && mb_stripos($menu['name'], $keyword) === false
                && mb_stripos(implode(' ', $menu['path']), $keyword) === false) {
                continue;
            }
            $matched[] = [
                'title' => $menu['title'],
                'name' => $menu['name'],
                'url' => $menu['href'],
                'level' => $menuLevel,
                'weigh' => $menu['weigh'],
                'path' => implode(' / ', $menu['path']),
            ];
            if (count($matched) >= $limit) {
                break;
            }
        }

        $result = [
            'keyword' => $keyword,
            'level' => $effectiveLevel ?: '全部',
            'total' => count($matched),
            'menus' => $matched,
            'format' => '用 Markdown 表格输出，列固定为：菜单 | 权限节点 | 权重；菜单列写成 [菜单标题](url) 保持可点击。',
        ];
        if ($canManage) {
            $result['manage'] = ['title' => '菜单管理', 'url' => 'auth.rule'];
            $result['format'] .= '表格后提醒用户可到 [菜单管理](auth.rule) 自行查看或编辑。';
        }
        return $result;
    }

    private function flattenMenus(array $nodes, array &$result, array $path): void
    {
        foreach ($nodes as $node) {
            if (!is_array($node)) {
                continue;
            }
            $title = (string)($node['title'] ?? '');
            $name = (string)($node['name'] ?? '');
            $children = $node['children'] ?? [];
            // 只收集侧边栏里可见、可跳转的菜单节点。
            if ((int)($node['type'] ?? 0) === 1 && (int)($node['ismenu'] ?? 0) === 1 && $name !== '') {
                $href = (string)($node['href'] ?? ($node['url'] ?? $name));
                if ($href !== '') {
                    $result[] = [
                        'title' => $title,
                        'name' => $name,
                        'href' => $href,
                        'weigh' => (int)($node['weigh'] ?? 0),
                        'path' => $path,
                    ];
                }
            }
            if (is_array($children) && $children !== []) {
                $childPath = $path;
                if ($title !== '') {
                    $childPath[] = $title;
                }
                $this->flattenMenus($children, $result, $childPath);
            }
        }
    }

    /** 插件功能以当前账号的实际菜单为准，不读取未授权的菜单定义。 */
    public function moduleNavigation(array $tree, string $module): array
    {
        $menus = [];
        $this->flattenMenus($tree, $menus, []);
        $result = [];
        foreach ($menus as $menu) {
            if (!str_starts_with($menu['name'], $module . '.')) continue;
            $result[] = [
                'title' => $menu['title'], 'name' => $menu['name'], 'url' => $menu['href'],
                'path' => implode(' / ', $menu['path']),
            ];
        }
        return $result;
    }

    public function search(string $keyword, int $limit = 10, int $pid = -1): array
    {
        $keyword = trim($keyword);
        $limit = max(1, min(200, $limit));
        $query = AdminRule::withoutField('condition,remark,create_time,update_time,py,pinyin');
        if ($pid >= 0) {
            $query->where('pid', $pid);
        }
        if ($keyword !== '') {
            $query->where(function ($query) use ($keyword): void {
                $query->where('title', 'like', '%' . $keyword . '%')
                    ->whereOr('name', 'like', '%' . $keyword . '%');
            });
        }
        $total = (clone $query)->count();
        $rows = $query->order('weigh', 'desc')->order('id', 'asc')->limit($limit)->select()->toArray();
        return [
            'total' => $total,
            'returned' => count($rows),
            'rules' => $rows,
        ];
    }

    public function create(array $input): array
    {
        $name = trim((string)($input['name'] ?? ''));
        $title = trim((string)($input['title'] ?? ''));
        if ($name === '' || !preg_match('/^[a-z0-9_\/.]+$/', $name)) {
            throw new \DomainException('规则名只能由小写字母、数字、下划线和 / . 组成');
        }
        if ($title === '') {
            throw new \DomainException('菜单标题不能为空');
        }
        if (AdminRule::where('name', $name)->find()) {
            throw new \DomainException('规则名已存在');
        }

        $type = (string)($input['type'] ?? '1');
        if (!in_array($type, ['0', '1', '2'], true)) {
            throw new \DomainException('菜单类型无效：0=目录，1=菜单，2=按钮');
        }
        $pid = (int)($input['pid'] ?? 0);
        if ($pid < 0 || ($pid > 0 && !AdminRule::find($pid))) {
            throw new \DomainException('父级菜单不存在');
        }
        $status = (string)($input['status'] ?? 'normal');
        if (!in_array($status, ['normal', 'hidden'], true)) {
            throw new \DomainException('状态只能是 normal 或 hidden');
        }
        $menutype = (string)($input['menutype'] ?? '_iframe');
        if (!in_array($menutype, ['_iframe', '_blank'], true)) {
            throw new \DomainException('菜单类型只能是 _iframe 或 _blank');
        }

        $data = [
            'name' => $name,
            'title' => $title,
            'pid' => $pid,
            'type' => (int)$type,
            'ismenu' => array_key_exists('ismenu', $input) ? ((int)$input['ismenu'] ? 1 : 0) : ($type === '2' ? 0 : 1),
            'url' => trim((string)($input['url'] ?? '')),
            'icon' => trim((string)($input['icon'] ?? '')) ?: 'fa fa-circle-o',
            'weigh' => (int)($input['weigh'] ?? 0),
            'is_quick' => (int)($input['is_quick'] ?? 0) ? 1 : 0,
            'menutype' => $menutype,
            'status' => $status,
            'condition' => '',
            'remark' => trim((string)($input['remark'] ?? '')),
        ];
        $rule = AdminRule::create($data);
        return ['id' => (int)$rule->id, 'name' => $rule->name, 'title' => $rule->title];
    }

    public function updateField(int $id, string $field, string $value): array
    {
        $field = trim($field);
        if (!in_array($field, self::EDITABLE, true)) {
            throw new \DomainException('该菜单字段不能修改');
        }
        $rule = AdminRule::find($id);
        if (!$rule) {
            throw new \DomainException('菜单规则不存在');
        }
        $rule->save($this->normalizeField($rule, $field, $value));
        return ['id' => $id, 'field' => $field, 'value' => $rule->getAttr($field)];
    }

    public function deletePreview(int $id): array
    {
        $rule = AdminRule::find($id);
        if (!$rule) {
            throw new \DomainException('菜单规则不存在');
        }
        return [
            'requires_confirmation' => true,
            'deleted' => false,
            'message' => '尚未删除。请向用户复述该菜单及其子菜单后，再以 confirm=true 调用 menu_rule_delete。',
            'rule' => $this->toRuleArray($rule),
            'descendant_count' => count($this->collectIds($id)) - 1,
        ];
    }

    public function delete(int $id): array
    {
        $rule = AdminRule::find($id);
        if (!$rule) {
            throw new \DomainException('菜单规则不存在');
        }
        $snapshot = $this->toRuleArray($rule);
        $ids = $this->collectIds($id);
        AdminRule::where('id', 'in', $ids)->delete();
        return ['deleted' => true, 'count' => count($ids), 'rule' => $snapshot];
    }

    public function batchPreview(int $parentId, string $field, string $value): array
    {
        $rule = $this->batchTarget($parentId, $field, $value);
        return [
            'requires_confirmation' => true,
            'updated' => false,
            'message' => '尚未修改。请向用户复述将影响的菜单数量与内容后，再以 confirm=true 调用 menu_rule_batch_update。',
            'parent' => $this->toRuleArray($rule),
            'field' => $field,
            'value' => $value,
            'affected_count' => count($this->childIds($parentId)),
        ];
    }

    public function batchUpdate(int $parentId, string $field, string $value): array
    {
        $rule = $this->batchTarget($parentId, $field, $value);
        $data = $this->normalizeField($rule, $field, $value);
        $ids = $this->childIds($parentId);
        AdminRule::where('id', 'in', $ids)->update($data);
        return [
            'updated' => true,
            'count' => count($ids),
            'field' => $field,
            'value' => $value,
            'parent' => $this->toRuleArray($rule),
        ];
    }

    /** 校验批量修改的分类及字段，返回分类节点。 */
    private function batchTarget(int $parentId, string $field, string $value): AdminRule
    {
        $field = trim($field);
        if (!in_array($field, self::BATCH_FIELDS, true)) {
            throw new \DomainException('批量修改只支持字段：' . implode('、', self::BATCH_FIELDS));
        }
        $rule = AdminRule::find($parentId);
        if (!$rule) {
            throw new \DomainException('菜单规则不存在');
        }
        if (!$this->childIds($parentId)) {
            throw new \DomainException('该菜单没有子菜单，无法批量修改');
        }
        $this->normalizeField($rule, $field, $value);
        return $rule;
    }

    private function normalizeField(AdminRule $rule, string $field, string $value): array
    {
        $value = trim($value);
        switch ($field) {
            case 'name':
                if (!preg_match('/^[a-z0-9_\/.]+$/', $value)) {
                    throw new \DomainException('规则名只能由小写字母、数字、下划线和 / . 组成');
                }
                if (AdminRule::where('name', $value)->where('id', '<>', (int)$rule->id)->find()) {
                    throw new \DomainException('规则名已存在');
                }
                return ['name' => $value];
            case 'title':
                if ($value === '') {
                    throw new \DomainException('菜单标题不能为空');
                }
                return ['title' => $value];
            case 'type':
                if (!in_array($value, ['0', '1', '2'], true)) {
                    throw new \DomainException('菜单类型无效：0=目录，1=菜单，2=按钮');
                }
                return ['type' => (int)$value];
            case 'ismenu':
            case 'is_quick':
                if (!in_array($value, ['0', '1'], true)) {
                    throw new \DomainException($field . ' 只能是 0 或 1');
                }
                return [$field => (int)$value];
            case 'status':
                if (!in_array($value, ['normal', 'hidden'], true)) {
                    throw new \DomainException('状态只能是 normal 或 hidden');
                }
                return ['status' => $value];
            case 'menutype':
                if (!in_array($value, ['_iframe', '_blank'], true)) {
                    throw new \DomainException('菜单类型只能是 _iframe 或 _blank');
                }
                return ['menutype' => $value];
            case 'weigh':
                if (!preg_match('/^-?\d+$/', $value)) {
                    throw new \DomainException('权重需要是整数');
                }
                return ['weigh' => (int)$value];
            case 'pid':
                $pid = (int)$value;
                if ($pid < 0) {
                    throw new \DomainException('父级菜单无效');
                }
                if ($pid === (int)$rule->id) {
                    throw new \DomainException('父级不能是自己');
                }
                if ($pid > 0) {
                    if (!AdminRule::find($pid)) {
                        throw new \DomainException('父级菜单不存在');
                    }
                    if (in_array($pid, $this->collectIds((int)$rule->id), true)) {
                        throw new \DomainException('父级不能是自己的子菜单');
                    }
                }
                return ['pid' => $pid];
            default:
                return [$field => $value];
        }
    }

    /** 收集规则及其所有子规则的 ID。 */
    private function collectIds(int $id): array
    {
        $rows = AdminRule::field('id,pid')->select()->toArray();
        $children = [];
        foreach ($rows as $row) {
            $children[(int)$row['pid']][] = (int)$row['id'];
        }
        $ids = [];
        $stack = [$id];
        while ($stack) {
            $current = array_pop($stack);
            if (in_array($current, $ids, true)) {
                continue;
            }
            $ids[] = $current;
            foreach ($children[$current] ?? [] as $child) {
                $stack[] = $child;
            }
        }
        return $ids;
    }

    /** 只包含子菜单，不含自身。 */
    private function childIds(int $id): array
    {
        $ids = $this->collectIds($id);
        array_shift($ids);
        return $ids;
    }

    private function toRuleArray(AdminRule $rule): array
    {
        return [
            'id' => (int)$rule->id,
            'name' => (string)$rule->name,
            'title' => (string)$rule->title,
            'type' => (string)$rule->type,
            'ismenu' => (int)$rule->ismenu,
            'weigh' => (int)$rule->weigh,
            'status' => (string)$rule->status,
        ];
    }
}

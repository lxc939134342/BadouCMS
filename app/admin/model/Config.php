<?php

// +----------------------------------------------------------------------
// | BADOUCMS [ 八斗网站系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2024-2030 http://doc.ldcode.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: lande <939134342@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\model;

use Throwable;
use think\Model;
use think\facade\Cache;
use InvalidArgumentException;

/**
 * 系统配置模型
 * @property mixed $content
 * @property mixed $rule
 * @property mixed $extend
 * @property mixed $allow_del
 */
class Config extends Model
{
    protected $name = 'config';

    public static string $cacheTag = 'sys_config';

    protected $append = [
        // 'value',
        'content_arr',
    ];

    protected array $jsonDecodeType = ['checkbox', 'selects'];
    protected array $needContent    = ['radio', 'checkbox', 'select', 'selects'];

    protected $typeList = [
        'string' => 'string',
        'textarea'   => 'textarea',
        'editor' => 'editor',
        'file' => 'file',
        'files' => 'files',
        'datetime'   => 'datetime',
        'image'   => 'image',
        'images' => 'images',
        'checkbox' => 'checkbox',
        'radio'    => 'radio',
        'switch'   => 'switch',
        'select'   => 'select',
    ];

    public function getTypeList()
    {
        return $this->typeList;
    }

    /**
     * 入库前
     * @throws Throwable
     */
    public static function onBeforeWrite(Config $model): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $model->getData('name'))) {
            throw new \think\Exception('配置名称只能包含字母、数字、下划线');
        }
        if (!in_array($model->getData('type'), $model->needContent)) {
            $model->content = null;
        }

        if (is_array($model->rule)) {
            $model->rule = implode(',', $model->rule);
        }
        $model->allow_del = 1;
    }

    /**
     * 写入后
     */
    public static function onAfterWrite(): void
    {
        // 清理配置缓存
        Cache::tag(self::$cacheTag)->clear();
    }

    public static function clearCache()
    {
        Cache::tag(self::$cacheTag)->clear();
    }

    public function getValueAttr($value, $row)
    {
        if (!isset($row['type']) || $value == '0') {
            return $value;
        }
        if (in_array($row['type'], $this->jsonDecodeType)) {
            return empty($value) ? [] : json_decode($value, true);
        } elseif ($row['type'] == 'switch') {
            return (bool)$value;
        } elseif ($row['type'] == 'editor') {
            return !$value ? '' : htmlspecialchars_decode($value);
        } elseif (in_array($row['type'], ['city', 'remoteSelects'])) {
            if (!$value) {
                return [];
            }
            if (!is_array($value)) {
                return explode(',', $value);
            }
            return $value;
        } else {
            return $value ?: '';
        }
    }

    public function setValueAttr(mixed $value, $row): mixed
    {
        if (in_array($row['type'], $this->jsonDecodeType)) {
            return $value ? json_encode($value) : '';
        } elseif ($row['type'] == 'switch') {
            return $value ? '1' : '0';
        } elseif ($row['type'] == 'time') {
            return $value ? date('H:i:s', strtotime($value)) : '';
        } elseif ($row['type'] == 'city') {
            if ($value && is_array($value)) {
                return implode(',', $value);
            }
            return $value ?: '';
        } elseif (is_array($value)) {
            return implode(',', $value);
        }

        return $value;
    }

    public function getContentAttr($value)
    {
        if ($value) {
            if (is_string($value)) {
                $value = json_decode($value, true);
            }
            if (is_array($value)) {
                return parse_array_string($value);
            }
            return $value;
        }
        return '';
    }

    public function setContentAttr($value)
    {
        if ($value) {
            if (is_string($value)) {
                $value = parse_array($value);
            }

            return json_encode($value);
        }
        return '';
    }

    public function getContentArrAttr($value, $row)
    {
        if (!isset($row['type'])) {
            return '';
        }
        if (in_array($row['type'], $this->needContent)) {
            $arr = is_array($row['content']) ? $row['content'] : json_decode($row['content'], true);
            return $arr ?: [];
        } else {
            return '';
        }
    }

    public function setGroup($key, $value)
    {
        $config_group = $this->where('name', 'config_group')->find();
        $config_group_value = json_decode($config_group['value'], true);
        $ishas = false;
        foreach ($config_group_value as $index => $item) {
            if ($item['key'] == $key) {
                $config_group_value[$index] = ['key' => $key, 'value' => $value];
                $ishas = true;
            }
        }
        if (!$ishas) {
            $config_group_value[] = ['key' => $key, 'value' => $value];
        }
        $config_group->value = json_encode($config_group_value);
        $config_group->save();
    }

    public function delGroup($key)
    {
        $config_group = $this->where('name', 'config_group')->find();
        $config_group_value = json_decode($config_group['value'], true);
        foreach ($config_group_value as $index => $item) {
            if ($item['key'] == $key) {
                unset($config_group_value[$index]);
            }
        }
        $config_group->value = json_encode($config_group_value);
        $config_group->save();
    }

    /**
     * 设置站点配置（批量创建或修改配置项）
     * @param array $data 配置项数组，每项为一条配置数据
     * @param bool $cover 覆盖数据
     * @return bool
     * @throws Throwable
     */
    public function setSysConfig(array $data, bool $cover = true): bool
    {
        // $data: [['name'=>'xxx', ...], ...]
        if (!is_array($data) || empty($data)) {
            throw new \Exception('参数错误');
        }

        // 检查name是否有重复
        $names = array_column($data, 'name');
        if (count($names) !== count(array_unique($names))) {
            throw new \Exception('参数中存在重复变量名');
        }
        // 查询已存在的name及id
        $existArr = $this->whereIn('name', $names)->column('id', 'name');

        $toInsert = [];
        $toUpdate = [];
        foreach ($data as $item) {
            if (empty($item['name'])) {
                throw new \Exception('变量名不能为空');
            }

            if (isset($existArr[$item['name']])) {
                // 只在 $cover 为 true 时才更新
                if ($cover) {
                    $item['id'] = $existArr[$item['name']];
                    $toUpdate[] = $item;
                }
                // 不覆盖时跳过
            } else {
                $toInsert[] = $item;
            }
        }
        // 检查新增项 name 是否有已存在
        if (!empty($toInsert)) {
            $conflict = array_intersect(array_keys($existArr), array_column($toInsert, 'name'));
            if ($conflict) {
                throw new \Exception('变量名已存在: ' . implode(',', $conflict));
            }
        }
        // 批量插入
        if (!empty($toInsert)) {
            $this->saveAll($toInsert);
        }
        // 批量更新
        if (!empty($toUpdate)) {
            $this->saveAll($toUpdate);
        }
        return true;
    }
}

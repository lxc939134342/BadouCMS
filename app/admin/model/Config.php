<?php

namespace app\admin\model;

use Throwable;
use think\Model;
use think\facade\Cache;

/**
 * 系统配置模型
 * @property mixed $content
 * @property mixed $rule
 * @property mixed $extend
 * @property mixed $allow_del
 */
class Config extends Model
{
    public static string $cacheTag = 'sys_config';

    protected $append = [
        'value',
        'content',
        'extend',
        'oldextend'
    ];

    protected array $jsonDecodeType = ['checkbox', 'array', 'selects'];
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
    public static function onBeforeInsert(Config $model): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $model->getData('name'))) {
            throw new \think\Exception('配置名称只能包含字母、数字、下划线');
        }
        if (!in_array($model->getData('type'), $model->needContent)) {
            $model->content = null;
        } else {
            $model->content = json_encode(str_attr_to_array($model->getData('content')));
        }
        if (is_array($model->rule)) {
            $model->rule = implode(',', $model->rule);
        }
        if ($model->getData('extend')) {
            $extend      = str_attr_to_array($model->getData('extend'));
            if ($extend) {
                $model->extend = json_encode($extend);
            }
        }
        $model->allow_del = 1;
    }

    public static function onBeforeWrite(Config $model): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $model->getData('name'))) {
            throw new \think\Exception('配置名称只能包含字母、数字、下划线');
        }
    }

    /**
     * 写入后
     */
    public static function onAfterWrite(): void
    {
        // 清理配置缓存
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

    public function getContentAttr($value, $row)
    {
        if (!isset($row['type'])) {
            return '';
        }
        if (in_array($row['type'], $this->needContent)) {
            $arr = json_decode($value, true);
            return $arr ?: [];
        } else {
            return '';
        }
    }

    public function getExtendAttr($value)
    {
        if ($value) {
            $arr = json_decode($value, true);
            if ($arr) {
                return $arr;
            }
        }
        return [];
    }

    public function getOldextendAttr($value, $row)
    {
        return $row['extend'];
    }
}

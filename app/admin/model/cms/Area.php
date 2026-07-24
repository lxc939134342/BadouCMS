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

namespace app\admin\model\cms;

use think\facade\Cache;
use think\facade\Event;
use think\Model;

/**
 * Area
 */
class Area extends Model
{
    // 表名
    protected $name = 'cms_area';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    protected $append = [
        'is_default_text'
    ];

    public function getIsDefaultTextAttr($value, $data)
    {
        return $data['is_default'] == 1 ? __('是') : __('否');
    }

    public static function onAfterWrite($model): void
    {
        Cache::delete('cms_default_lang');
        Cache::delete('cms_area');

        $data = $model->getData();
        /* 设置默认 */
        if (isset($data['is_default']) && $data['is_default'] == 1) {
            self::where('id', '<>', $model->id)->update(['is_default' => 0]);
        }
        $acode = (string) ($data['acode'] ?? '');
        if ($acode !== '') {
            self::langPack($acode, true);
        }
    }

    public static function onAfterInsert($model): void
    {
        $data = $model->getData();
        $acode = (string) ($data['acode'] ?? '');
        $langPack = self::langPack($acode, true);

        Event::trigger('cms_area_create_after', array_merge([
            'area' => $data,
            'acode' => $acode,
        ], $langPack));
    }

    public static function onAfterUpdate($model): void
    {
        $data = $model->getData();
        $origin = $model->getOrigin();
        $acode = (string) ($data['acode'] ?? '');
        $oldAcode = (string) ($origin['acode'] ?? '');

        if ($acode !== '' && $oldAcode !== '' && $acode !== $oldAcode) {
            Event::trigger('cms_area_update_after', [
                'area' => $data,
                'acode' => $acode,
                'old_acode' => $oldAcode,
            ]);
        }
    }

    public static function onBeforeDelete($model): void
    {
        $data = $model->getData();

        // 检测是否为默认区域
        if ($data['is_default'] == 1) {
            throw new \Exception(__('The default region is not allowed for deletion'));
        }

        // 检测区域是否处于开启状态
        if ($data['status'] == 1) {
            throw new \Exception(__('Cannot delete area that is currently enabled'));
        }

        // 检测是否为中文区域
        if ($data['acode'] == 'cn') {
            // 检测是否有其他区域存在
            $otherAreasCount = self::where('acode', '<>', 'cn')->count();
            if ($otherAreasCount == 0) {
                throw new \Exception(__('Chinese area cannot be deleted when there are no other areas'));
            }
        }

        // 检测该区域下是否有栏目
        $contentSortCount = \app\admin\model\cms\ContentSort::where('acode', $data['acode'])->count();
        if ($contentSortCount > 0) {
            throw new \Exception(__('Cannot delete area with existing categories'));
        }

        // 检测该区域下是否有内容
        $contentCount = \app\admin\model\cms\Content::where('acode', $data['acode'])->count();
        if ($contentCount > 0) {
            throw new \Exception(__('Cannot delete area with existing content'));
        }
    }

    public function areaList()
    {
        $langs = $this->where('status', 1)->order('is_default DESC,id ASC')->column('id,acode,name');
        return $langs;
    }

    public function defaultArea()
    {
        $area = $this->where('is_default', 1)->field('id,acode,name')->find();
        return $area;
    }

    public static function onAfterDelete($model): void
    {
        Cache::delete('cms_default_lang');
        Cache::delete('cms_area');

        $data = $model->getData();
        $acode = (string) ($data['acode'] ?? '');
        $langPack = self::langPack($acode);

        // 清空该区域的站点配置
        \app\admin\model\cms\Site::where('acode', $data['acode'])->delete();

        // 清空该区域的公司信息
        \app\admin\model\cms\Company::where('acode', $data['acode'])->delete();

        Event::trigger('cms_area_delete_after', [
            'area' => $data,
            'acode' => $acode,
            'lang' => $langPack['lang'],
        ]);
    }

    protected static function langPack(string $acode, bool $create = false): array
    {
        $lang = strtolower(str_replace('_', '-', trim($acode)));
        $lang = $lang === 'cn' ? 'zh-cn' : $lang;

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $lang)) {
            throw new \InvalidArgumentException(__('Invalid language code'));
        }

        $data = ['lang' => $lang];
        if (!$create) {
            return $data;
        }

        foreach (['index', 'api'] as $module) {
            $langPath = root_path() . 'app' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR;
            if (!is_dir($langPath) && !mkdir($langPath, 0755, true) && !is_dir($langPath)) {
                throw new \RuntimeException(__('Unable to create language directory'));
            }

            $target = $langPath . $lang . '.php';
            if (!is_file($target)) {
                $source = '';
                foreach (['zh-cn', 'en'] as $template) {
                    $file = $langPath . $template . '.php';
                    if ($template !== $lang && is_file($file)) {
                        $source = $file;
                        break;
                    }
                }

                if ($source) {
                    if (!copy($source, $target)) {
                        throw new \RuntimeException(__('Unable to create language file'));
                    }
                } elseif (file_put_contents($target, "<?php\n\nreturn [];\n", LOCK_EX) === false) {
                    throw new \RuntimeException(__('Unable to create language file'));
                }
            }

            $data[$module . '_lang_file'] = $target;
        }
        return $data;
    }
}

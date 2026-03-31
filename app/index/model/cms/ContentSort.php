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

namespace app\index\model\cms;

use think\Model;
use think\facade\Db;

class ContentSort extends Model
{
    protected $name = 'cms_content_sort';

    protected $append = ['ico', 'pic', 'link'];

    public static function formatIco($value): string
    {
        return $value ? cdnurl($value) : '';
    }
    public static function formatUrl($value, $data)
    {
        if ($data['outlink']) {
            return $data['outlink'];
        }
        return bdurl($data['type'], $data['urlname'], 'list', $data['scode'], $data['filename'], '', '');
    }


    public function getIcoAttr($value, $data)
    {
        return self::formatIco($value);
    }

    public function getPicAttr($value, $data)
    {
        return self::formatIco($value);
    }

    public function getLinkAttr($value, $data)
    {
        return self::formatUrl($value, $data);
    }

    protected static function aucodeMapKey()
    {
        return 'aucodekey_' . get_frontend_lang();
    }

    /**
     * 获取缓存标签和时长
     * @param string $type
     * @param array  $tag
     * @return array
     */
    public static function getCacheKeyExpire($type, $tag = [])
    {
        $config = [
            'cachelifetime' => 3600 * 24
        ];
        $cache = !isset($tag['cache']) ? $config['cachelifetime'] : $tag['cache'];
        $cache = in_array($cache, ['true', 'false', true, false], true) ? (in_array($cache, ['true', true], true) ? 0 : -1) : (int)$cache;
        $cacheKey = $cache > -1 ? "cms-taglib-{$type}-" . md5(serialize($tag)) : false;
        $cacheExpire = $cache > -1 ? $cache : null;
        return [$cacheKey, $cacheExpire];
    }

    /**
     * 返回空数据
     * @return void
     */
    public function getDefaultData(): array
    {
        return [
            'scode' => 0,
            'tcode' => 0,
            'pcode' => 0,
            'pic' => '',
            'name' => '',
            'subname' => '',
            'toplink' => '',
            'toprows' => 0,
        ];
    }

    /**
     * 获取分类信息
     * @param mixed $scode | $urlname
     * @return mixed
     */
    public function getSort($scode): array|false
    {
        $field = array(
            'a.*',
            'a.name AS parentname',
            'b.type',
            'b.urlname',
            'd.gcode'
        );
        $info = $this->alias('a')->field($field)
            ->whereOr(['a.scode' => $scode, 'a.filename' => $scode])
            ->join('cms_model b', 'a.mcode=b.mcode', 'LEFT')
            ->join('user_level d', 'a.gid=d.id', 'LEFT')
            ->where('acode', get_frontend_lang())
            ->find();
        if (!$info) {
            return false;
        }
        $info = $info->toArray();
        return $info;
    }

    // 获取默认语言的urlname
    public function getSortNotLang($scode)
    {
        $field = array(
            'a.*',
            'a.name AS parentname',
            'b.type',
            'b.urlname',
            'd.gcode'
        );

        $info = $this->alias('a')->field($field)
            ->whereOr(['a.scode' => $scode, 'a.filename' => $scode])
            ->join('cms_model b', 'a.mcode=b.mcode', 'LEFT')
            ->join('user_level d', 'a.gid=d.id', 'LEFT')
            ->order('id asc')
            ->find();
        if (!$info) {
            return false;
        }
        $info = $info->toArray();
        set_forntend_lang($info['acode']);

        return $info;
    }

    /**
     * 多个分类信息，不区分语言，兼容跨语言
     * @param mixed $scodes
     * @param mixed $aucodes  语言统一调用编码
     * @return array
     */
    public static function getMultSort($scodes = null, $aucodes = null): array
    {
        if ($scodes) {
            $out_data = ContentSort::getSortsTree(false, 0);
            $scode_arr = explode(',', $scodes);
        } elseif ($aucodes) {
            $out_data = ContentSort::getSortsTree(true, 0);
            $aucodemap = cache(self::aucodeMapKey());
            $aucodes_arr = explode(',', $aucodes);
            $scode_arr = [];
            foreach ($aucodes_arr as $v) {
                $scode_arr[] = $aucodemap[$v];
            }
        }

        // 读取指定分类
        if ($scode_arr) {
            $filtered_data = [];
            foreach ($scode_arr as $index => $value) {
                $filtered_data[$value] = $out_data[$value];
            }

            if (count($filtered_data) > 1) {
                $scode_pos_map = array_flip($scode_arr);
                // 将数组按照scode_arr的顺序排序
                usort($filtered_data, function ($a, $b) use ($scode_pos_map) {
                    // 直接从映射表中获取位置，O(1) 复杂度
                    $pos_a = $scode_pos_map[$a['scode']] ?? PHP_INT_MAX;
                    $pos_b = $scode_pos_map[$b['scode']] ?? PHP_INT_MAX;
                    return $pos_a - $pos_b;
                });
            }
        }

        $key = 0;
        $result = [];
        foreach ($filtered_data as $index => $value) { // 按查询的数据条数循环
            $parent = $out_data[$value['pcode']];
            $top = $out_data[$value['tcode']];
            $value['n'] = $key;
            $value['i'] = $key + 1;
            $value['toplink'] = self::formatUrl($value['tcode'], $top);
            $value['parentlink'] = self::formatUrl($value['pcode'],  $parent);
            $value['ico'] = self::formatIco($value['ico']);
            $value['pic'] = self::formatIco($value['pic']);
            $value['link'] = self::formatUrl($value['link'], $value);
            $result[$index] = $value;
            $key++;
        }
        return $result;
    }


    /**
     * 分类栏目列表关系树
     * @param mixed $filter_acode 是否区分站点编号
     * @param mixed $is_status    1:只获取状态为1的分类 0:获取所有分类
     * @return array
     */
    public static function getSortsTree($filter_acode = true, $is_status = 1): array
    {
        $fields = array(
            'a.*',
            'b.type',
            'b.urlname'
        );
        $order = 'a.pcode,a.sorting,a.id desc';

        if ($filter_acode) {
            $cacheKey = 'sorts_tree_' . get_frontend_lang();
            list($cacheKey, $cacheExpire) = self::getCacheKeyExpire($cacheKey);

            $result = Db::name('cms_content_sort')->alias('a')
                ->where('a.acode', get_frontend_lang())
                ->join('cms_model b', 'a.mcode=b.mcode', 'LEFT')
                ->field($fields)
                ->cache($cacheKey, $cacheExpire, 'cms_cache')
                ->order($order)
                ->select();
            $aucodemap = cache(self::aucodeMapKey());
            if (!$aucodemap) {
                $aucodemap = $result->column('scode', 'aucode');
                $aucodemap = array_filter($aucodemap, fn($key) => !empty($key), ARRAY_FILTER_USE_KEY);
                cache(self::aucodeMapKey(), $aucodemap);
            }
        } else {
            $cacheKey = 'cms_sorts_tree_all';
            list($cacheKey, $cacheExpire) = self::getCacheKeyExpire($cacheKey);
            $result =  Db::name('cms_content_sort')->alias('a')
                ->join('cms_model b', 'a.mcode=b.mcode', 'LEFT')
                ->field($fields)
                ->cache($cacheKey, $cacheExpire, 'cms_cache')
                ->order($order)
                ->select();
        }

        if ($result->isEmpty()) {
            return [];
        }

        $result = $result->toArray();

        $nodes = [];
        $tree = [];
        $top = [];

        // 一次性获取所有栏目的内容数量，避免循环中重复查询
        $allSortRows = self::getAllSortRows();
        // 构建父子关系映射
        $parentMap = [];
        foreach ($result as $value) {
            if ($value['pcode'] && $value['pcode'] != 0) {
                $parentMap[$value['pcode']][] = $value['scode'];
            }
        }

        // 递归获取所有子栏目（使用映射，避免重复遍历）
        $subScodesMap = [];
        foreach ($result as $value) {
            $subscode = [];
            $queue = [$value['scode']];
            while (!empty($queue)) {
                $current = array_shift($queue);
                $subscode[] = $current;
                if (isset($parentMap[$current])) {
                    foreach ($parentMap[$current] as $child) {
                        if (!in_array($child, $subscode)) {
                            $queue[] = $child;
                        }
                    }
                }
            }
            $subScodesMap[$value['scode']] = $subscode;
        }

        // 预计算所有栏目的顶级编码，避免重复递归
        $topCodeMap = [];
        foreach ($result as $value) {
            $current = $value['scode'];
            while ($current && $current != 0) {
                $parent = null;
                foreach ($result as $item) {
                    if ($item['scode'] == $current) {
                        $parent = $item['pcode'];
                        break;
                    }
                }
                if ($parent == 0 || $parent == $current) {
                    $topCodeMap[$value['scode']] = $current;
                    break;
                }
                $current = $parent;
            }
        }

        foreach ($result as $value) {
            // 过滤掉关闭的分类
            if ($is_status == 1 && $value['status'] != 1) {
                continue;
            }
            $value['soncount'] = 0;
            // 使用预计算的子栏目关系
            $subscode = $subScodesMap[$value['scode']] ?? [];
            $value['rows'] = array_sum(array_intersect_key($allSortRows, array_flip($subscode)));

            $nodes[$value['scode']] = $value;
        }

        foreach ($nodes as $scode => &$node) {
            /* 上级名称与链接 */
            $node['parentname'] = '';
            $node['parentlink'] = '';
            $node['parentrows'] = 0;

            /* 顶级名称与链接 */
            $node['tcode'] = $scode;
            $node['topname'] = '';
            $node['toplink'] = '';
            $node['toprows'] = 0;

            //顶级栏目
            if (isset($node['pcode']) && $node['pcode'] == 0) {
                $top[] = &$node;
            }

            //子栏目树
            if (isset($node['pcode']) && $node['pcode']) {
                /* 上级名称与链接 */
                $node['parentname'] = $nodes[$node['pcode']]['name'];
                $node['parentrows'] = $nodes[$node['pcode']]['rows'];

                /* 顶级编号、名称与链接 */
                $node['tcode'] = $topCodeMap[$node['scode']] ?? $node['scode'];
                $node['topname'] = $nodes[$node['tcode']]['name'];
                $node['toprows'] = $nodes[$node['tcode']]['rows'];

                /* 统计子栏目数量 */
                $nodes[$node['pcode']]['soncount']++;

                // 记录到关系树
                $tree[$node['pcode']]['son'][] = &$node;
            }
        }

        unset($node);
        $nodes['top'] = $top;
        $nodes['tree'] = $tree;

        return $nodes;
    }

    public function getSortList(): array
    {
        $fields = array(
            'a.id',
            'a.pcode',
            'a.scode',
            'a.name',
            'a.filename',
            'a.outlink',
            'b.type',
            'b.urlname'
        );
        $order = 'a.pcode,a.sorting,a.id desc';
        $sorts = $this->alias('a')
            ->where("a.acode='" . get_frontend_lang() . "'")
            ->join('cms_model b', 'a.mcode=b.mcode', 'LEFT')
            ->field($fields)
            ->order($order)
            ->cache('__CACHE_CMS_SORTS_' . get_frontend_lang(), 3600, 'cms_cache')
            ->select();
        if ($sorts->isEmpty()) {
            return [];
        }

        $sorts = $sorts->toArray();
        $result = [];
        foreach ($sorts as $key => $value) {
            $result[$value['scode']] = $value;
        }
        return $result;
    }

    // 获取分类名称
    public function getSortName($scode): mixed
    {
        $result = $this->getSortList();
        return $result[$scode]['name'];
    }

    // 分类顶级编码
    public function getTopParent($scode, $sorts): mixed
    {
        if (!$scode || !$sorts) {
            return false;
        }
        if (!isset($sorts[$scode])) {
            return false;
        }

        // 优化：使用迭代代替递归，避免递归栈开销
        $visited = []; // 防止循环引用
        $current = $scode;

        while ($current && $current != 0) {
            // 检查循环引用
            if (isset($visited[$current])) {
                return $current; // 发现循环，返回当前节点
            }
            $visited[$current] = true;

            // 检查是否到达顶级
            if (!isset($sorts[$current])) {
                return $current;
            }

            $parent = $sorts[$current]['pcode'];

            // 检查自引用或到达顶级
            if ($parent == 0 || $parent == $current) {
                return $current;
            }

            $current = $parent;
        }

        return $current;
    }

    // 分类顶级编码
    public function getSortTopScode($scode)
    {
        $result = $this->getSortList();
        return $this->getTopParent($scode, $result) ?: $scode;
    }

    // 获取位置
    public function getPosition($scode)
    {
        $result = $this->getSortList();
        $position = [];

        // 优化：使用迭代代替递归，直接构建路径
        $visited = []; // 防止循环引用
        $current = $scode;

        while ($current && $current != 0) {
            // 检查循环引用
            if (isset($visited[$current])) {
                break;
            }
            $visited[$current] = true;

            if (!isset($result[$current])) {
                break;
            }

            $position[] = $result[$current];
            $parent = $result[$current]['pcode'];

            // 检查是否到达顶级
            if ($parent == 0 || $parent == $current) {
                break;
            }

            $current = $parent;
        }

        return array_reverse($position);
    }

    // 分类子类集
    public function getSubScodes($scode, $sorts = null)
    {
        if (!$scode) {
            return [];
        }

        // 第一次调用时初始化
        if ($sorts === null) {
            $sorts = $this->getSortList();
        }

        // 检查自引用（父级等于自己）
        if (isset($sorts[$scode]) && $sorts[$scode]['pcode'] == $scode) {
            return [$scode];
        }

        // 优化：使用BFS遍历，避免递归和重复遍历
        $scodes = [];
        $queue = [$scode];
        $visited = [$scode => true]; // 避免重复访问

        while (!empty($queue)) {
            $current = array_shift($queue);
            $scodes[] = $current;

            // 查找所有直接子分类
            foreach ($sorts as $sort) {
                if ($sort['pcode'] == $current && empty($sort['outlink'])) {
                    $childScode = $sort['scode'];
                    // 避免重复添加和循环
                    if (!isset($visited[$childScode])) {
                        $visited[$childScode] = true;
                        $queue[] = $childScode;
                    }
                }
            }
        }

        return $scodes;
    }

    // 指定分类标签调用
    public function getSortTags($scode)
    {
        $scode_arr = [];
        if ($scode) {
            // 获取所有子类分类编码
            $this->scodes = []; // 先清空
            $scodes = $this->getSubScodes(trim($scode)); // 获取子类

            // 构建查询条件
            $scode_arr = function ($query) use ($scodes, $scode) {
                $query->whereIn('a.scode', $scodes)
                    ->whereOr('a.subscode', $scode);
            };
        }

        $result = \app\index\model\cms\Content::alias('a')
            ->where('c.type', 2)
            ->where('a.tags', '<>', '')
            ->where($scode_arr)
            ->join('cms_content_sort b', 'a.scode=b.scode', 'LEFT')
            ->join('cms_model c', 'b.mcode=c.mcode', 'LEFT')
            ->where('a.status', 1)
            ->order('a.visits', 'DESC')
            ->column('a.tags');

        return $result;
    }

    /**
     * 批量获取所有分类的内容数量
     */
    protected static function getAllSortRows()
    {
        $cacheKey = 'sort_content_rows_' . get_frontend_lang();
        list($cacheKey, $cacheExpire) = self::getCacheKeyExpire($cacheKey);
        $result = cache($cacheKey);
        if ($result) {
            return $result;
        }

        // 基础条件
        $where = [
            ['acode', '=', get_frontend_lang()],
            ['status', '=', 1],
            ['date', '<', date('Y-m-d H:i:s')]
        ];

        // 获取主分类统计
        $mainCounts = Content::where($where)
            ->group('scode')
            ->column('count(*)', 'scode');

        // 修改副分类统计，排除空值
        $subCounts = Content::where($where)
            ->where('subscode', '<>', '')
            ->whereNotNull('subscode')
            ->group('subscode')
            ->column('count(*)', 'subscode');

        // 合并统计结果
        $result = [];
        foreach ($mainCounts as $scode => $count) {
            if ($scode !== '' && $scode !== null) {
                $result[$scode] = ($result[$scode] ?? 0) + $count;
            }
        }
        foreach ($subCounts as $scode => $count) {
            if ($scode !== '' && $scode !== null) {
                $result[$scode] = ($result[$scode] ?? 0) + $count;
            }
        }

        // 设置缓存
        cache($cacheKey, $result, $cacheExpire, 'cms_cache');

        return $result;
    }

    // 获取分类内容数量
    public function getSortRows($scode)
    {
        $allCounts = $this->getAllSortRows();
        if (!$scode) {
            return array_sum($allCounts);
        }

        $subscode = $this->getSubScodes($scode);

        $subRows = array_sum(array_intersect_key($allCounts, array_flip($subscode)));
        return $subRows;
    }

    // 获取分类树
    public function getSorts($acode)
    {
        $fields = [
            'a.*',
            'b.type'
        ];
        $result = $this->alias('a')
            ->field($fields)
            ->where('a.acode', $acode)
            ->where('a.status', 1)
            ->join('cms_model b', 'a.mcode=b.mcode', 'LEFT')
            ->order(['a.pcode' => 'asc', 'a.sorting' => 'asc', 'a.id' => 'asc'])
            ->select()
            ->toArray();

        return get_tree($result, 0, 'scode', 'pcode');
    }


    // 获取分类的子类
    public function getSortsSon($acode, $scode)
    {
        $fields = [
            'a.*',
            'b.type'
        ];

        $result = $this->alias('a')
            ->field($fields)
            ->where('a.pcode', $scode)
            ->where('a.acode', $acode)
            ->where('a.status', 1)
            ->join('cms_model b', 'a.mcode=b.mcode', 'LEFT')
            ->order(['a.sorting' => 'asc', 'a.id' => 'asc'])
            ->select()
            ->toArray();

        return $result;
    }

    /**
     * 获取导航列表
     * @param mixed $parent
     * @param mixed $scode
     * @param mixed $num
     * @return void
     */
    public static function navList($parent = 0, $scode = false, $num = false): array
    {
        // 将scode转换为数组，避免重复调用explode
        $scode_arr = [];
        if ($scode) {
            $scode_arr = explode(',', $scode);
        }
        $data = ContentSort::getSortsTree();
        if ($parent) { // 非顶级栏目起始,调用子栏目
            $parent_arr = explode(',', $parent);
            $out_data = [];
            // 优化：直接添加元素避免array_merge开销，缓存trim结果
            foreach ($parent_arr as $vp) {
                $trimmed_vp = trim($vp);
                if (!empty($trimmed_vp) && isset($data['tree'][$trimmed_vp]['son']) && is_array($data['tree'][$trimmed_vp]['son'])) {
                    // 直接添加元素，避免array_merge的开销
                    foreach ($data['tree'][$trimmed_vp]['son'] as $son_item) {
                        $out_data[] = $son_item;

                        // 如果指定了数量限制，提前结束
                        if ($num && count($out_data) >= (int)$num) {
                            break 2; // 跳出两层循环
                        }
                    }
                }
            }
        } else { // 顶级栏目起始
            $out_data = $data['top'];
        }
        // 确保操作的是数组
        $out_data = is_array($out_data) ? $out_data : [];

        // 读取指定数量
        if ($num) {
            $out_data = array_slice($out_data, 0, (int)$num);
        }

        // 读取指定分类
        if ($scode_arr) {
            // 创建scode到位置的映射，提高查找效率
            $scode_pos_map = array_flip($scode_arr);
            // 创建scode的集合，提高查找效率
            $scode_set = array_flip($scode_pos_map);

            // 直接构建新数组，避免使用unset
            $filtered_data = [];
            foreach ($out_data as $value) {
                if (isset($scode_pos_map[$value['scode']])) {
                    // 保存位置信息，用于后续排序
                    $value['_sort_pos'] = $scode_pos_map[$value['scode']];
                    $filtered_data[] = $value;
                }
            }

            // 只有当数组中有多个元素时才进行排序
            if (count($filtered_data) > 1) {
                // 使用位置信息进行排序，避免每次比较都调用array_search
                usort($filtered_data, function ($a, $b) {
                    return $a['_sort_pos'] - $b['_sort_pos'];
                });
            }

            // 移除排序时添加的临时字段
            foreach ($filtered_data as &$item) {
                unset($item['_sort_pos']);
            }

            // 更新out_data为过滤和排序后的结果
            $out_data = $filtered_data;
        }
        $key = 0;
        $result = [];
        foreach ($out_data as $index => $value) { // 按查询的数据条数循环
            $parent = $data[$value['pcode']];
            $top = $data[$value['tcode']];
            $value['toplink'] = self::formatUrl($value['tcode'], $top);
            $value['parentlink'] = self::formatUrl($value['pcode'],  $parent);
            $value['n'] = $key;
            $value['i'] = $key + 1;
            $value['ico'] = self::formatIco($value['ico']);
            $value['pic'] = self::formatIco($value['pic']);
            $value['link'] = self::formatUrl($value['link'], $value);
            $result[$index] = $value;
            $key++;
        }
        return $result;
    }

    /* 生成面包屑html */
    public static function positionHtml($params): string
    {
        $scode = $params['scode'];
        $separator = $params['separator'];
        $indextext = $params['indextext'] ? $params['indextext'] : __('Home');
        $separatoricon = $params['separatoricon'];
        $indexicon = $params['indexicon'];
        // 已经设置图标，则图标优先，如果没有，则判断是否已经设置文字
        if ($separatoricon) {
            $separator = ' <i class="' . $separatoricon . '"></i> ';
        }

        if ($indexicon) {
            $indextext = '<i class="' . $indexicon . '"></i>';
        }

        $out_html = '<a href="' . url('/') . '">' . $indextext . '</a> ';
        $data = (new self())->getPosition($scode);
        foreach ($data as $key => $value) {
            if ($value['outlink']) {
                $out_html .= $separator . ' <a href="' . $value['outlink'] . '">' . $value['name'] . '</a> ';
            } else {
                $out_html .= $separator . ' <a href="' . $value['link'] . '">' . $value['name'] . '</a> ';
            }
        }
        return $out_html;
    }
}

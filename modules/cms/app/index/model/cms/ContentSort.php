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
use think\facade\View;

class ContentSort extends Model
{
    protected $name = 'cms_content_sort';

    protected $append = ['ico','pic','link'];

    // 存储分类查询数据
    protected $sorts = null;

    // 存储分类及子编码
    protected $scodes = [];

    // 存储栏目位置
    protected $position = [];

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

    public function getIcoAttr($value, $data)
    {
        return $value ? full_url($value) : '';
    }

    public function getPicAttr($value, $data)
    {
        return $value ? full_url($value) : '';
    }

    public function getLinkAttr($value, $data)
    {
        if ($data['outlink']) {
            return $data['outlink'];
        }
        return bdurl($data['type'], $data['urlname'], 'list', $data['scode'], $data['filename'], '', '');
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
            ->join('cms_member_group d', 'a.gid=d.id', 'LEFT')
            ->find();
        if (!$info) {
            return false;
        }
        $info = $info->toArray();
        return $info;
    }

    // 多个分类信息，不区分语言，兼容跨语言
    public static function getMultSort($scodes): array
    {
        $field = array(
            'a.*',
            'a.name AS parentname',
            'b.type',
            'b.urlname'
        );
        $order = 'a.pcode,a.sorting,a.id desc';
        $scode_arr = [];
        if ($scodes) {
            $scode_arr = explode(',', $scodes);
        }

        $view = View::instance();
        $data = self::alias('a')->field($field)
            ->where('a.scode', 'in', $scode_arr)
            ->join('cms_model b', 'a.mcode=b.mcode', 'LEFT')
            ->order($order)
            ->filter(function ($row) use ($view) {
                $row['tcode'] = $view->sort['tcode'] ?? 0;
                $row['topname'] = $view->sort['topname'] ?? '';
                $row['toplink'] = $view->sort['toplink'] ?? '';
                $row['parentname'] = $view->sort['parentname'] ?? '';
                $row['parentlink'] = $view->sort['parentlink'] ?? '';
            })
            ->select();
        $data = $data->toArray();
        if (is_array($scode_arr)) {
            //将数组按照scode_arr的顺序排序
            usort($data, function ($a, $b) use ($scode_arr) {
                $aIndex = array_search($a['scode'], $scode_arr);
                $bIndex = array_search($b['scode'], $scode_arr);
                return $aIndex - $bIndex;
            });
        }

        return $data;
    }

    // 分类栏目列表关系树
    public static function getSortsTree()
    {
        $fields = array(
            'a.*',
            'b.type',
            'b.urlname'
        );
        $order = 'a.pcode,a.sorting,a.id desc';
        $view = View::instance();
        $result = self::alias('a')
            ->where('a.acode', get_frontend_lang())
            ->where('a.status', 1)
            ->join('cms_model b', 'a.mcode=b.mcode', 'LEFT')
            ->field($fields)
            ->cache('cms_sorts_tree_' . get_frontend_lang(), 3600, 'cms_cache')
            ->order($order)
            ->filter(function ($row) use ($view) {
                $row['tcode'] = $view->sort['tcode'] ?? 0;
                $row['topname'] = $view->sort['topname'] ?? '';
                $row['toplink'] = $view->sort['toplink'] ?? '';
                $row['parentname'] = $view->sort['parentname'] ?? '';
                $row['parentlink'] = $view->sort['parentlink'] ?? '';
                $row['parentrows'] = 0;
                $row['toprows'] = 0;
            })
            ->select();

        if ($result->isEmpty()) {
            return [];
        }
        $result = $result->toArray();
        $data = [];
        $tree = [];
        $top = [];
        $self = new self();
        foreach ($result as $value) {
            $value['soncount'] = 0;
            $sortrows = $self->getSortRows($value['scode']);
            $value['rows'] = $sortrows['rows'] ?? 0;
            $data[$value['scode']] = $value;
            if (isset($value['pcode']) && $value['pcode']) {
                $tree[$value['pcode']]['son'][] = $value; // 记录到关系树
            }
        }
        // 统计son数量
        foreach ($tree as $key => $value) {
            $tree[$key]['soncount'] = count($value['son']);
            $data[$key]['soncount'] = count($tree[$key]['son']);
        }
        foreach ($data as $key => $value) {
            if (isset($value['pcode']) && $value['pcode'] == 0) {
                $top[] = $value;
            }
        }
        $data['top'] = $top;
        $data['tree'] = $tree;
        return $data;
    }

    public function getSortList(): array
    {
        if (!$this->sorts) {
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
                ->cache('__CACHE_CMS_SORTS_'. get_frontend_lang(), 3600, 'cms_cache')
                ->select();
            if ($sorts->isEmpty()) {
                return [];
            }

            $sorts = $sorts->toArray();
            $result = [];
            foreach ($sorts as $key => $value) {
                $result[$value['scode']] = $value;
            }
            $this->sorts = $result;
        }
        return $this->sorts;
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
        if (! $scode || ! $sorts) {
            return false;
        }
        if (!isset($sorts[$scode])) {
            return false;
        }
        $this->position[] = $sorts[$scode];
        if ($sorts[$scode]['pcode']) {
            return $this->getTopParent($sorts[$scode]['pcode'], $sorts);
        } else {
            return $sorts[$scode]['scode'];
        }
    }

    // 分类顶级编码
    public function getSortTopScode($scode)
    {
        $result = $this->getSortList();
        return $this->getTopParent($scode, $result);
    }

    // 获取位置
    public function getPosition($scode)
    {
        $result = $this->getSortList();
        $this->position = []; // 重置
        $this->getTopParent($scode, $result);
        return array_reverse($this->position);
    }

    // 分类子类集
    public function getSubScodes($scode, $sorts = null)
    {
        if (! $scode) {
            return;
        }
        // 第一次调用时初始化
        if ($sorts === null) {
            $this->scodes = [];  // 重置结果数组
            $sorts = $this->getSortList();
        }
        $this->scodes[] = $scode;
        // 查找所有直接子分类
        foreach ($sorts as $sort) {
            if ($sort['pcode'] == $scode && empty($sort['outlink'])) {
                $this->getSubScodes($sort['scode'], $sorts);
            }
        }
        return $this->scodes;
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
    protected function getAllSortRows()
    {
        // 使用缓存
        $cacheKey = 'all_sort_rows_' . get_frontend_lang();
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
            ->where('subscode', '<>', '')    // 添加这行
            ->whereNotNull('subscode')       // 保留这行
            ->group('subscode')
            ->column('count(*)', 'subscode');

        // 合并统计结果
        $result = [];
        foreach ($mainCounts as $scode => $count) {
            if ($scode !== '' && $scode !== null) {  // 添加空值检查
                $result[$scode] = ($result[$scode] ?? 0) + $count;
            }
        }
        foreach ($subCounts as $scode => $count) {
            if ($scode !== '' && $scode !== null) {  // 添加空值检查
                $result[$scode] = ($result[$scode] ?? 0) + $count;
            }
        }
        // 设置缓存
        cache($cacheKey, $result, 3600, 'cms_cache');

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
        $scode_arr = [];
        if ($scode) {
            $scode_arr = explode(',', $scode);
        }

        $data = ContentSort::getSortsTree();
        if ($parent) { // 非顶级栏目起始,调用子栏目
            $parent_arr = explode(',', $parent);
            $out_data = [];
            foreach ($parent_arr as $vp) {
                if (isset($data['tree'][trim($vp)]['son'])) {
                    $out_data = array_merge($out_data, $data['tree'][trim($vp)]['son']);
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
            foreach ($out_data as $index => $value) {
                if (!in_array($value['scode'], $scode_arr)) {
                    unset($out_data[$index]);
                }
            }
            // 将数组按照scode_arr的顺序排序
            // 将数组按照scode_arr的顺序排序
            usort($out_data, function ($a, $b) use ($scode_arr) {
                $pos_a = array_search($a['scode'], $scode_arr);
                $pos_b = array_search($b['scode'], $scode_arr);
                return $pos_a - $pos_b;
            });
        }
        $key = 0;
        $result = [];
        foreach ($out_data as $index => $value) { // 按查询的数据条数循环
            $value['n'] = $key;
            $value['i'] = $key + 1;
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

        $out_html = '<a href="' .url('/'). '">' . $indextext . '</a> ';
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

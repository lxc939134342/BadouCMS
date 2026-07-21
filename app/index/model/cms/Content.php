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

use think\facade\Cache;
use think\facade\Db;
use think\Model;

class Content extends Model
{
    private const FILTER_CACHE_TTL = 600;
    private const FILTER_RANGE_PATTERN = '/^(?=.*\d)\s*(\d+(?:\.\d+)?)?\s*-\s*(\d+(?:\.\d+)?)?\s*$/';

    protected $name = "cms_content";

    protected $append = [
        'link',
        'sortlink',
        'subsortlink',
        'enclosuresize',
        'likeslink',
        'opposelink'
    ];

    public function getContentAttr($value): string
    {
        if (!$value) {
            return '';
        }

        $value = replace_keyword($value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 移除危险的事件属性和 script 标签
        $value = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);
        $value = preg_replace('/\s(on\w+)\s*=\s*["\']?[^"\']*["\']?/i', '', $value);
        $value = preg_replace('/javascript:/i', '', $value);

        return $value;
    }

    public function getDescriptionAttr($value): string
    {
        return $value ? strip_tags(parse_markdown($value)) : '';
    }

    public function getIcoAttr($value, $data)
    {
        return $value ? cdnurl($value) : '';
    }

    public function getPicsAttr($value, $data)
    {
        if ($value) {
            $pics = explode(',', $value);
            if (count($pics) == 1) {
                return cdnurl($pics[0]);
            }
            foreach ($pics as &$pic) {
                $pic = cdnurl($pic);
            }
            unset($pic);
            return implode(',', $pics);
        }
        return '';
    }

    public function getLinkAttr($value, $data)
    {
        if (!isset($data['type']) || !isset($data['urlname']) || !isset($data['sortfilename'])) {
            return '';
        }
        if (isset($data['outlink']) && $data['outlink']) {
            return $data['outlink'];
        }
        return (string) bdurl($data['type'], $data['urlname'], 'content', $data['scode'], $data['sortfilename'], $data['id'], $data['filename']);
    }

    public function getSortLinkAttr($value, $data)
    {
        if (!isset($data['type']) || !isset($data['urlname']) || !isset($data['sortfilename'])) {
            return '';
        }
        if (isset($data['outlink']) && $data['outlink']) {
            return $data['outlink'];
        }
        return (string) bdurl($data['type'], $data['urlname'], 'list', $data['scode'], $data['sortfilename']);
    }

    public function getSubSortLinkAttr($value, $data)
    {
        if (!isset($data['type']) || !isset($data['urlname']) || !isset($data['sortfilename'])) {
            return '';
        }

        if (isset($data['outlink']) && $data['outlink']) {
            return $data['outlink'];
        }

        return (string) bdurl($data['type'], $data['urlname'], 'list', $data['subscode'], $data['subfilename']);
    }

    public function getEnclosureSizeAttr($value, $data)
    {
        if (empty($data['enclosure'])) {
            return '';
        }

        // 移除路径穿越字符
        $enclosure = str_replace(['../', '..\\', '..'], '', $data['enclosure']);

        // 构建完整路径
        $fullPath = public_path() . $enclosure;

        // 使用 realpath 获取真实路径
        $realPath = realpath($fullPath);
        $publicPath = realpath(public_path());

        // 验证文件必须在 public 目录内
        if ($realPath && $publicPath && strpos($realPath, $publicPath) === 0 && file_exists($realPath)) {
            return filesize($realPath);
        }

        return '';
    }

    public function getLikesLinkAttr($value, $data)
    {
        return (string) url('/do/likes', ['id' => $data['id']]);
    }

    public function getOpposeLinkAttr($value, $data)
    {
        return (string) url('/do/oppose', ['id' => $data['id']]);
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
     * 获取 cms_content 表的所有字段（用于白名单验证）
     * @return array
     */
    protected static function getAllowedFields()
    {
        static $fields = null;

        if ($fields === null) {
            try {
                // 获取 cms_content 表的所有字段
                $columns = Db::query("SHOW COLUMNS FROM " . config('database.connections.mysql.prefix') . "cms_content");
                $fields = array_column($columns, 'Field');
            } catch (\Exception $e) {
                // 如果查询失败，使用默认白名单
                $fields = [
                    'id',
                    'title',
                    'author',
                    'source',
                    'keywords',
                    'description',
                    'content',
                    'ico',
                    'pics',
                    'date',
                    'istop',
                    'isrecommend',
                    'isheadline',
                    'visits',
                    'likes',
                    'oppose',
                    'status',
                    'outlink',
                    'type',
                    'scode',
                    'subscode',
                    'filename',
                    'urlname',
                    'create_time',
                    'update_time',
                    'sorting',
                    'enclosure'
                ];
            }
        }

        return $fields;
    }

    // 内容详情页图片
    public function getContentPics($id, $field, $num = 0, $onlypic = false)
    {
        $result = $this->alias('a')
            ->field($field . ',picstitle')
            ->join('cms_content_ext b', 'a.id=b.contentid', 'LEFT')
            ->where('a.id', $id)
            ->where('a.status', 1)
            ->where('a.date', '<', date('Y-m-d H:i:s'))
            ->find();
        $data = [];
        if ($result && $result['pics']) {
            $pics = explode(',', $result['pics']);
            $picstitle = explode(',', $result->picstitle);
            if ($num) {
                // 限制标签数量
                $pics = array_slice($pics, 0, $num);
            }
            if ($onlypic) {
                return $pics;
            }

            foreach ($pics as $key => $pic) {
                $data[] = [
                    'n' => $key,
                    'i' => $key + 1,
                    'src' => $pic,
                    'title' => isset($picstitle[$key]) ? $picstitle[$key] : '',
                ];
            }
        }
        return $data;
    }

    // 指定内容标签调用
    public static function getContentTags($id = '', $scode = '', $num = 0, $target = 'list')
    {
        $data = [];
        $sortModel = new ContentSort();
        if ($id) {
            $result = self::field('scode,tags')
                ->where('id', $id)
                ->where('status', 1)
                ->where('status=1')
                ->where('date', '<', date('Y-m-d H:i:s'))
                ->find();
            if ($result && $result->tags) {
                $tags = explode(',', $result->tags);
                $scode = $scode ?: $result->scode;
                $sort =  $sortModel->getSort($scode); // 获取栏目信息
                foreach ($tags as $key => $value) {
                    $data[] = array(
                        'sort' => $sort,
                        'tags' => $value
                    );
                }
            }
        } elseif ($scode) {
            $scodes = explode(',', $scode); // 多个栏目是分别获取
            foreach ($scodes as $key => $value) {
                $sort = $sortModel->getSort($value); // 获取栏目信息
                if (!!$result = $sortModel->getSortTags($value)) {
                    $tags = implode(',', $result); // 把栏目tags串起来
                    $tags = array_unique(explode(',', $tags)); // 再把所有tags组成数组并去重
                    foreach ($tags as $key2 => $value2) {
                        if (! in_array($value2, array_column($data, 'tags'))) { // 避免重复输出
                            $data[] = array(
                                'sort' => $sort,
                                'tags' => $value2
                            );
                        }
                    }
                }
            }
        } else {
            // 全部栏目时候强制标签页形式
            $target = 'tag';
            if (!!$result = $sortModel->getSortTags('')) {
                $tags = implode(',', $result); // 把栏目tags串起来
                $tags = array_unique(explode(',', $tags)); // 再把所有tags组成数组并去重
                foreach ($tags as $key2 => $value2) {
                    if (! in_array($value2, array_column($data, 'tags'))) { // 避免重复输出
                        $data[] = array(
                            'tags' => $value2
                        );
                    }
                }
            }
        }

        foreach ($data as $key => &$value) {
            $value['n'] = $key;
            $value['i'] = $key + 1;

            if ($target == 'tag') {
                $value['link'] = url('/tag/' . $value['tags']);
            } else {
                $value['link'] = bdurl($value['sort']['type'], $value['sort']['urlname'], 'list', $value['sort']['scode'], $value['sort']['filename'], '', '') . '?tag=' . urlencode($value['tags']);
            }
            $value['text'] = $value['tags'];
        }

        if ($num > 0) {
            $data = array_slice($data, 0, $num);
        }

        return $data;
    }

    /**
     * 单篇内容
     * @param mixed $scode
     * @param mixed $id
     * @return mixed
     */
    public static function getContent($scode = null, $id = null)
    {
        $field = array(
            'a.*',
            'b.name as sortname',
            'b.filename as sortfilename',
            'c.name as subsortname',
            'c.filename as subfilename',
            'd.type',
            'd.name as modelname',
            'd.urlname',
            'e.*',
            'f.gcode'
        );

        $where = [];
        if ($scode) {
            $where[] = ['a.scode|b.filename', '=', $scode];
        }
        if ($id) {
            $where[] = ['a.id|a.filename', '=', $id];
        }
        $result = self::alias('a')
            ->field($field)
            ->where($where)
            ->where('a.status', 1)
            ->join('cms_content_sort b', 'a.scode=b.scode', 'LEFT')
            ->join('cms_content_sort c', 'a.subscode=c.scode', 'LEFT')
            ->join('cms_model d', 'b.mcode=d.mcode', 'LEFT')
            ->join('cms_content_ext e', 'a.id=e.contentid', 'LEFT')
            ->join('user_level f', 'a.gid=f.id', 'LEFT')
            ->order('id DESC')
            ->find();
        if (!$result) {
            return [];
        }
        $result['subsortlink'] = $result['subscode'] ? bdurl($result['type'], $result['urlname'], 'list', $result['subscode'], $result['subfilename'], '', '') : '';
        $result->inc('visits')->save();
        // 内容详情页解析 markdown
        if ($result->content) {
            $result->content = parse_markdown($result->content);
        }
        $tagsModel = new Tags();
        if (! ! $tags = $tagsModel->getTags()) {
            // 将A链接保护起来,alt、titel保护起来
            $rega = "/(<a .*?>.*?<\/a>)|([a-zA-Z-]+\s*=\s*['\"][^'\"]*['\"])/i";
            preg_match_all($rega, $result->content, $matches1);
            foreach ($matches1[0] as $key => $value) {
                $result->content = str_replace($value, '#rega:' . $key . '#', $result->content);
            }

            // 去除包含关系的短tags,实现长关键字优先
            foreach ($tags as $key => $value) {
                foreach ($tags as $key2 => $value2) {
                    if (strpos($value2['name'], $value['name']) !== false && $key != $key2) {
                        unset($tags[$key]);
                    }
                }
            }
            // 执行内链替换
            foreach ($tags as $value) {
                $result->content = preg_replace('/' . $value['name'] . '/', '<a href="' . $value['link'] . '">' . $value['name'] . '</a>', $result->content, get_sys_config('content_tags_replace_num') ?: 3);
            }

            // 还原保护的内容
            $pattern = '/\#rega:([0-9]+)\#/';
            if (preg_match_all($pattern, $result->content, $matches2)) {
                $count = count($matches2[0]);
                for ($i = 0; $i < $count; $i++) {
                    $result->content = str_replace($matches2[0][$i], $matches1[0][$matches2[1][$i]], $result->content);
                }
            }
        }
        $result->content = html_entity_decode($result->content);

        return $result;
    }

    // 上一篇或下一篇内容
    public static function getContentPreNext($scodes, $id, $type = 'next')
    {
        $field = array(
            'a.id',
            'a.title',
            'a.filename',
            'a.ico',
            'a.scode',
            'b.filename as sortfilename',
            'c.type',
            'c.urlname'
        );

        $data = [];
        $where = [
            ['a.scode', 'in', $scodes],
            ['a.acode', '=', get_frontend_lang()],
            ['a.status', '=', 1],
            ['a.date', '<', date('Y-m-d H:i:s')],
        ];

        if ($type == 'next') {
            $order = 'a.id ASC';
            $where[] = ['a.id', '>', $id];
            /* 暂无内容 */
            $data['nextcontent'] = "<a href='javascript:;'>" . __('Not have') . "</a>";
            $data['nextlink'] = "javascript:;";
            $data['nexttitle'] = __('Not have');
            $data['nextico'] = "";
        } else {
            $order = 'a.id DESC';
            $where[] = ['a.id', '<', $id];
            /* 暂无内容 */
            $data['precontent'] = "<a href='javascript:;'>" . __('Not have') . "</a>";
            $data['prelink'] = "javascript:;";
            $data['pretitle'] = __('Not have');
            $data['preico'] = "";
        }

        $content = self::alias('a')->field($field)
            ->where($where)
            ->join('cms_content_sort b', 'a.scode=b.scode', 'LEFT')
            ->join('cms_model c', 'b.mcode=c.mcode', 'LEFT')
            ->order($order)
            ->find();

        if (!$content) {
            return $data;
        }
        if ($type == 'next') {
            $data['nextcontent'] = "<a href='" . $content['link'] . "'>" . $content['title'] . "</a>";
            $data['nextlink'] = $content['link'];
            $data['nexttitle'] = $content['title'];
            $data['nextico'] = $content['ico'];
        } else {
            $data['precontent'] = "<a href='" . $content['link'] . "'>" . $content['title'] . "</a>";
            $data['prelink'] = $content['link'];
            $data['pretitle'] = $content['title'];
            $data['preico'] = $content['ico'];
        }

        return $data;
    }

    /**
     * 构建/解析扩展字段的查询条件
     * 提取出通用方法，并开放完整的查询生成机制由外部事件接管
     * @param array $get
     * @param array $params
     * @return array
     */
    public static function buildExtWhere($get, $params = []): array
    {
        // 1. 触发事件：无限制全面接管扩展条件生成权
        // 如果有钩子监听并赋值了 `ext_where`，则直接使用钩子的返回结果并结束本方法
        $eventObj = (object)['get' => $get, 'params' => $params, 'ext_where' => null];
        \think\facade\Event::trigger(static::class . '.BuildExtWhere', $eventObj);

        if ($eventObj->ext_where !== null && is_array($eventObj->ext_where)) {
            return $eventObj->ext_where;
        }

        $ext_where = [];
        $fuzzy = $params['fuzzy'] ?? false;
        $filterData = self::normalizeExtFilters((array)$get);

        foreach ($filterData['filters'] as $key => $value) {
            $fieldType = $filterData['types'][$key] ?? 1;
            $isMultiDbField = in_array($fieldType, [4, 14], true);
            $configuredOptions = $filterData['options'][$key] ?? [];

            // 后台存在同名选项时按普通文本处理，例如 10-20ml 不是数值区间。
            $isConfiguredOption = in_array($value, $configuredOptions, true);
            if (!$isConfiguredOption && preg_match(self::FILTER_RANGE_PATTERN, $value, $range)) {
                if ($range[1] !== '') {
                    $ext_where[] = [$key, '>', $range[1]];
                }
                if ($range[2] !== '') {
                    $ext_where[] = [$key, '<=', $range[2]];
                }
            } elseif (strpos($value, ',') !== false) {
                $multiValues = array_filter(array_map('trim', explode(',', $value)), static fn($item) => $item !== '');
                if ($isMultiDbField) {
                    $ext_where[] = function ($query) use ($key, $multiValues) {
                        foreach ($multiValues as $item) {
                            $query->whereOr($key, 'find in set', $item);
                        }
                    };
                } else {
                    $ext_where[] = [$key, 'in', $multiValues];
                }
            } elseif ($fuzzy) {
                $ext_where[] = [$key, 'like', '%' . $value . '%'];
            } elseif ($isMultiDbField) {
                $ext_where[] = [$key, 'find in set', $value];
            } else {
                $ext_where[$key] = $value;
            }
        }

        return $ext_where;
    }

    /**
     * 只规范化扩展筛选：正常选项保持原功能，伪造字段和值不进入数据库查询。
     */
    protected static function normalizeExtFilters(array $get): array
    {
        static $requestCache = [];

        $cacheKey = md5(serialize($get));
        if (isset($requestCache[$cacheKey])) {
            return $requestCache[$cacheKey];
        }

        $definitions = [];
        foreach (Extfield::field('name,type,value')->select()->toArray() as $row) {
            $name = (string)$row['name'];
            if (!preg_match('/^ext_[a-zA-Z0-9_]+$/', $name)) {
                continue;
            }
            $definitions[$name] = [
                'type' => (int)$row['type'],
                'options' => array_values(array_map('strval', parse_array((string)$row['value']))),
            ];
        }

        $filters = [];
        $types = [];
        $options = [];
        $queryString = (string)request()->server('QUERY_STRING', '');
        if (strlen($queryString) <= 2048) {
            foreach ($get as $field => $rawValue) {
                if (!is_string($field) || !str_starts_with($field, 'ext_') || !isset($definitions[$field])) {
                    continue;
                }
                if (!is_string($rawValue) && !is_numeric($rawValue)) {
                    continue;
                }

                $value = trim((string)$rawValue);
                if ($value === '') {
                    continue;
                }

                $allowedValues = $definitions[$field]['options'];
                $isNumericRange = (bool)preg_match(self::FILTER_RANGE_PATTERN, $value);
                if ($allowedValues && !$isNumericRange) {
                    $values = array_values(array_unique(array_intersect(
                        array_filter(array_map('trim', explode(',', $value)), static fn($item) => $item !== ''),
                        $allowedValues
                    ), SORT_STRING));
                    if (!$values) {
                        continue;
                    }
                    sort($values, SORT_STRING);
                    $value = implode(',', $values);
                }

                $filters[$field] = $value;
                $types[$field] = $definitions[$field]['type'];
                $options[$field] = $allowedValues;
            }
        }

        ksort($filters, SORT_STRING);
        return $requestCache[$cacheKey] = compact('filters', 'types', 'options');
    }

    protected static function getFilterQuery(array $query, array $filters): array
    {
        foreach (array_keys($query) as $key) {
            if (is_string($key) && str_starts_with($key, 'ext_')) {
                unset($query[$key]);
            }
        }
        foreach ($filters as $field => $value) {
            $query[$field] = $value;
        }
        ksort($query, SORT_STRING);
        return $query;
    }

    protected static function getFilterCacheKey(array $params, array $query, string $lang, int $num, $order): string
    {
        ksort($params, SORT_STRING);
        $payload = [
            'version' => 2,
            'host' => request()->host(),
            'path' => request()->baseUrl(),
            'lang' => $lang,
            'params' => $params,
            'query' => $query,
            'num' => $num,
            'order' => is_string($order) ? $order : '',
        ];

        return 'cms-filter-list-' . hash('sha256', serialize($payload));
    }

    /**
     * 内容列表
     * @param mixed $params
     * @return array
     */
    public static function contentList($params): array
    {
        $scode = $params['scode'];
        $ext_table = false;
        $lg = get_frontend_lang();
        $lfield = ''; // 查询字段限制
        $order = 'a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.sorting ASC,a.date DESC,a.id DESC'; // 默认排序
        $simple = false; //简洁分页
        $num = 12;    //如果不传入分页，那么最多获取16条数据
        $page = false;
        $start = 0;
        $filterWhere = []; //筛选条件
        $tagWhere = [];
        $where = [
            ['a.status', '=', 1],
            ['d.type', '=', 2],
            ['a.date', '<', date('Y-m-d H:i:s')],
        ];

        $data = [
            'total' => 0,
            'data' => [],
            'page' => [
                'index'   => '',
                'pre'     => '',
                'next'    => '',
                'last'    => '',
                'bar'     => '',
                'current' => '',
                'count'   => '',
                'rows'    => '',
                'number'  => ''
            ]
        ];

        if (!$scode) {
            return $data;
        }
        // 分离参数
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'num':
                    $num = $value;
                    break;
                case 'order':
                    switch ($value) {
                        case 'id':
                            $order = 'a.id DESC,a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.sorting ASC,a.date DESC';
                            break;
                        case 'date':
                            $order = 'a.date DESC,a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.sorting ASC,a.id DESC';
                            break;
                        case 'sorting':
                            $order = 'a.sorting ASC,a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.date DESC,a.id DESC';
                            break;
                        case 'istop':
                            $order = 'a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.sorting ASC,a.date DESC,a.id DESC';
                            break;
                        case 'isrecommend':
                            $order = 'a.isrecommend DESC,a.istop DESC,a.isheadline DESC,a.sorting ASC,a.date DESC,a.id DESC';
                            break;
                        case 'isheadline':
                            $order = 'a.isrecommend DESC,a.istop DESC,a.isheadline DESC,a.sorting ASC,a.date DESC,a.id DESC';
                            break;
                        case 'visits':
                        case 'likes':
                        case 'oppose':
                            $order = $value . ' DESC,a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.sorting ASC,a.date DESC,a.id DESC';
                            break;
                        case 'random': // 随机取数
                            $order = Db::raw("RAND()");
                            $params['cache'] = false;
                            break;
                        default:
                            if ($value) {
                                // 白名单：从数据库动态获取允许的排序字段
                                $allowedFields = self::getAllowedFields();
                                $allowedDirections = ['ASC', 'DESC', 'asc', 'desc'];

                                $orders = explode(',', $value);
                                $validOrders = [];

                                foreach ($orders as $v) {
                                    $v = trim($v);
                                    // 分离字段名和排序方向
                                    $parts = preg_split('/\s+/', $v);
                                    $field = $parts[0];
                                    $direction = strtoupper($parts[1] ?? 'DESC');

                                    // 验证排序方向
                                    if (!in_array($direction, $allowedDirections)) {
                                        $direction = 'DESC';
                                    }

                                    // 处理扩展字段 ext_
                                    if (strpos($field, 'ext_') === 0) {
                                        $fieldName = substr($field, 4); // 移除 ext_ 前缀
                                        if (preg_match('/^[a-zA-Z0-9_]+$/', $fieldName)) {
                                            $validOrders[] = 'e.' . $field . ' ' . $direction;
                                        }
                                    } elseif (in_array($field, $allowedFields)) {
                                        $validOrders[] = 'a.' . $field . ' ' . $direction;
                                    }
                                }

                                if (!empty($validOrders)) {
                                    $order = implode(',', $validOrders) . ',a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.sorting ASC,a.date DESC,a.id DESC';
                                }
                            }
                    }
                    break;
                case 'filter':
                    if ($value) {
                        $filter = explode('|', $value);
                        if (count($filter) == 2) {
                            $filter_arr = explode(',', $filter[1]);
                            if ($filter[0] == 'title') {
                                $filter[0] = 'a.title';
                            }
                            foreach ($filter_arr as $value) {
                                if ($value) {
                                    if ($params['fuzzy']) {
                                        $filterWhere[] = [$filter[0], 'like', "%" . escape_string($value) . "%"];
                                    } else {
                                        $filterWhere[] = [$filter[0], '=', escape_string($value)];
                                    }
                                }
                            }
                        }
                    }

                    break;
                case 'tags':
                    if ($value) {
                        $tags_arr = explode(',', $value);
                        foreach ($tags_arr as $value) {
                            if ($value) {
                                if ($params['fuzzy']) {
                                    $tagWhere[] = ['a.tags', 'like', "%" . escape_string($value) . "%"];
                                } else {
                                    $tagWhere[] = ['a.tags', '=', escape_string($value)];
                                }
                            }
                        }
                    }
                    break;
                case 'ispics':
                    $eq = $value ? '<>' : '=';
                    $where[] = ["a.pics", $eq, ""];
                    break;
                case 'isico':
                    $eq = $value ? '<>' : '=';
                    $where[] = ["a.ico", $eq, ""];
                    break;
                case 'istop':
                    $where[] = ["a.istop", '=', $value];
                    break;
                case 'isrecommend':
                    $where[] = ["a.isrecommend", '=', $value];
                    break;
                case 'isheadline':
                    $where[] = ["a.isheadline", '=', $value];
                    break;
                case 'page':
                    $page = $value;
                    break;
                case 'start':
                    // 起始数校验
                    if (is_numeric($value)) {
                        $start = $value;
                    } else {
                        $start = 0;
                    }
                    break;
                case 'lfield':
                    $lfield = $value;
                    break;
            }
        }

        $filterQuery = [];
        $filterCacheKey = null;
        $filterCacheEnabled = false;
        if ($page) {
            $filterData = self::normalizeExtFilters((array)request()->get());
            $filterQuery = self::getFilterQuery((array)request()->get(), $filterData['filters']);
            if (!isset($filterQuery['tag']) && request()->param('tag')) {
                $filterQuery['tag'] = request()->param('tag');
                ksort($filterQuery, SORT_STRING);
            }
            $filterCacheEnabled = ($params['cache'] ?? true) !== false;

            if ($filterCacheEnabled) {
                $filterCacheKey = self::getFilterCacheKey($params, $filterQuery, $lg, (int)$num, $order);
                $cachedData = Cache::get($filterCacheKey);
                if (is_array($cachedData)) {
                    return $cachedData;
                }
            }
        }

        if ($lfield) {
            $lfield .= ',id,outlink,type,scode,sortfilename,filename,urlname'; // 附加必须字段
            $fields = explode(',', $lfield);
            $fields = array_unique($fields); // 去重

            // 白名单：从数据库动态获取允许的字段
            $allowedFields = self::getAllowedFields();

            foreach ($fields as $key => $value) {
                $value = trim($value);

                if (strpos($value, 'ext_') === 0) {
                    // 扩展字段：验证字段名格式
                    $extFieldName = substr($value, 4);
                    if (preg_match('/^[a-zA-Z0-9_]+$/', $extFieldName)) {
                        $ext_table = true;
                        $fields[$key] = 'e.' . $value;
                    } else {
                        unset($fields[$key]); // 非法字段移除
                    }
                } elseif ($value == 'sortname') {
                    $fields[$key] = 'b.name as sortname';
                } elseif ($value == 'sortfilename') {
                    $fields[$key] = 'b.filename as sortfilename';
                } elseif ($value == 'subsortname') {
                    $fields[$key] = 'c.name as subsortname';
                } elseif ($value == 'subfilename') {
                    $fields[$key] = 'c.filename as subfilename';
                } elseif ($value == 'type' || $value == 'urlname') {
                    $fields[$key] = 'd.' . $value;
                } elseif ($value == 'modelname') {
                    $fields[$key] = 'd.name as modelname';
                } elseif (in_array($value, $allowedFields)) {
                    $fields[$key] = 'a.' . $value;
                } else {
                    unset($fields[$key]); // 非白名单字段移除
                }
            }
        } else {
            $ext_table = true;
            $fields = array(
                'a.*',
                'b.name as sortname',
                'b.filename as sortfilename',
                'c.name as subsortname',
                'c.filename as subfilename',
                'd.type',
                'd.name as modelname',
                'd.urlname',
                'e.*',
                'f.gcode'
            );
        }

        $scode_arr = [];
        if ($scode && $scode != '*') {
            // 获取所有子类分类编码
            $arr = explode(',', $scode); // 传递有多个分类时进行遍历
            $arr = array_map('trim', $arr);
            $arr = array_filter($arr);
            $contentSortModel = new ContentSort();
            $scodes = $arr;
            foreach ($arr as $value) {
                // 使用直接数组追加替代 array_merge
                foreach ($contentSortModel->getSubScodes($value) as $subCode) {
                    $scodes[] = $subCode;
                }
            }
            // 去重，避免重复的分类编码
            $scodes = array_unique($scodes);
            if (!empty($scodes)) {
                $scode_arr = [
                    ['a.scode', 'in', $scodes],
                    ['a.subscode', 'find in set', implode(',', $scodes)]
                ];
                $where[] = function ($query) use ($scode_arr) {
                    $query->whereOr($scode_arr);
                };
            }
        }

        if ($lg) {
            $where[] = [
                'a.acode',
                '=',
                $lg
            ];
        }
        if ($page) {
            $tag = request()->param('tag');
            if ($tag) {
                if ($params['fuzzy']) {
                    $tagWhere[] = ['a.tags', 'like', "%" . escape_string($tag) . "%"];
                } else {
                    $tagWhere[] = ['a.tags', '=', escape_string($tag)];
                }
            }
        }

        // 筛选条件支持模糊匹配
        $db = self::name('cms_content')
            ->alias('a')
            ->field($fields)
            ->where($where)
            ->whereOr($filterWhere)
            ->where(function ($query) use ($tagWhere) {
                $query->whereOr($tagWhere);
            })
            ->join('cms_content_sort b', 'a.scode=b.scode', 'LEFT')
            ->join('cms_content_sort c', 'a.subscode=c.scode', 'LEFT')
            ->join('cms_model d', 'b.mcode=d.mcode', 'LEFT')
            ->join('user_level f', 'a.gid=f.id', 'LEFT');

        // 获取扩展字段表
        if ($ext_table) {
            $db->join('cms_content_ext e', 'a.id=e.contentid', 'LEFT');
        }

        $db->order($order);
        if ($page) {
            // 扩展字段数据筛选
            $ext_where = self::buildExtWhere(request()->get(), $params);
            $db->where($ext_where);
            $loadPage = static function () use ($db, $filterQuery, $num, $simple, $data) {
                $res = $db->paginate([
                    'query' => $filterQuery,
                    'list_rows' => $num,
                ], $simple);

                $data['total'] = $res->total();
                if (!$res->isEmpty()) {
                    $data['per_page'] = $res->listRows();
                    $data['current_page'] = $res->currentPage();
                    $data['last_page'] = $res->lastPage();
                    $data['data'] = $res->getCollection()->toArray();
                    $data['page'] = $res->pageData();
                }
                return $data;
            };

            if ($filterCacheEnabled && $filterCacheKey) {
                return Cache::tag('cms_cache')->remember(
                    $filterCacheKey,
                    static function () use ($filterCacheKey, $loadPage) {
                        // 等待同一筛选请求完成后再次读缓存，避免并发请求重复执行 SQL。
                        $cachedData = Cache::get($filterCacheKey);
                        return is_array($cachedData) ? $cachedData : $loadPage();
                    },
                    self::FILTER_CACHE_TTL
                );
            }
            $data = $loadPage();
        } else {
            list($cacheKey, $exprie) = self::getCacheKeyExpire('contentList', $params);
            $res = $db->limit($start, $num)->cache($cacheKey, $exprie, 'cms_cache')->select();
            if (!$res->isEmpty()) {
                $data['total'] = $res->count();
                $data['data'] = $res->toArray();
            }
        }

        return $data;
    }

    /**
     * 获取指定分类的内容
     * @param mixed $scode
     * @return array|\think\Collection
     */
    public function getSortContent($scode)
    {
        $fields = array(
            'a.id',
            'a.filename',
            'a.date',
            'c.type',
            'c.urlname',
            'b.scode',
            'b.filename as sortfilename'
        );

        $where = [
            ['a.status', '=', 1],
            ['c.type', '=', 2],
            ['a.date', '<', date('Y-m-d H:i:s')],
            ['a.scode', '=', $scode],
        ];

        return $this->alias('a')
            ->field($fields)
            ->where($where)
            ->join('cms_content_sort b', 'a.scode=b.scode', 'LEFT')
            ->join('cms_model c', 'b.mcode=c.mcode', 'LEFT')
            ->select();
    }

    /**
     * 搜索列表
     * @param mixed $params
     * @return array
     */
    public static function searchList($params): array
    {
        $ext_table = false;
        $lg = get_frontend_lang();
        $lfield = ''; // 查询字段限制
        $order = 'a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.sorting ASC,a.date DESC,a.id DESC'; // 默认排序
        $simple = false; //简洁分页
        $num = 1000; //如果不传入分页，那么最多获取1000条数据
        $page = false;
        $start = 0;
        $filterWhere = []; //筛选条件
        $tagWhere = [];
        $scode = request()->param('scode', '');
        $keyword = request()->param('keyword');
        $field = request()->param('field', '');
        //禁止搜索过滤域名
        if (preg_match("/\.[a-z]{2,}/i", $keyword)) {
            $keyword = "";
        }
        if ($keyword) {
            $keyword = strip_tags($keyword);
            $keyword = str_replace(strrchr($keyword, "."), "", $keyword);  //去掉带有后缀的关键词
            $keyword = mb_substr($keyword, 0, 15);
        }

        if (!preg_match('/^[\w\|\s]+$/', $field)) {
            $field = '';
        }

        $where = [
            ['a.status', '=', 1],
            ['d.type', '=', 2],
            ['a.date', '<', date('Y-m-d H:i:s')],
        ];

        $data = [
            'total' => 0,
            'data' => [],
            'page' => [
                'index'   => '',
                'pre'     => '',
                'next'    => '',
                'last'    => '',
                'bar'     => '',
                'current' => '',
                'count'   => '',
                'rows'    => '',
                'number'  => ''
            ]
        ];

        // 分离参数
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'field':
                    $field = $value;
                    break;
                case 'num':
                    $num = $value;
                    break;
                case 'order':
                    switch ($value) {
                        case 'id':
                            $order = 'a.id DESC,a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.sorting ASC,a.date DESC';
                            break;
                        case 'date':
                            $order = 'a.date DESC,a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.sorting ASC,a.id DESC';
                            break;
                        case 'sorting':
                            $order = 'a.sorting ASC,a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.date DESC,a.id DESC';
                            break;
                        case 'istop':
                            $order = 'a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.sorting ASC,a.date DESC,a.id DESC';
                            break;
                        case 'isrecommend':
                            $order = 'a.isrecommend DESC,a.istop DESC,a.isheadline DESC,a.sorting ASC,a.date DESC,a.id DESC';
                            break;
                        case 'isheadline':
                            $order = 'a.isrecommend DESC,a.istop DESC,a.isheadline DESC,a.sorting ASC,a.date DESC,a.id DESC';
                            break;
                        case 'visits':
                        case 'likes':
                        case 'oppose':
                            $order = $value . ' DESC,a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.sorting ASC,a.date DESC,a.id DESC';
                            break;
                        case 'random': // 随机取数
                            $order = Db::raw("RAND()");
                            break;
                        default:
                            if ($value) {
                                // 白名单：从数据库动态获取允许的排序字段
                                $allowedFields = self::getAllowedFields();
                                $allowedDirections = ['ASC', 'DESC', 'asc', 'desc'];

                                $orders = explode(',', $value);
                                $validOrders = [];

                                foreach ($orders as $v) {
                                    $v = trim($v);
                                    // 分离字段名和排序方向
                                    $parts = preg_split('/\s+/', $v);
                                    $field = $parts[0];
                                    $direction = strtoupper($parts[1] ?? 'DESC');

                                    // 验证排序方向
                                    if (!in_array($direction, $allowedDirections)) {
                                        $direction = 'DESC';
                                    }

                                    // 处理扩展字段 ext_
                                    if (strpos($field, 'ext_') === 0) {
                                        $fieldName = substr($field, 4); // 移除 ext_ 前缀
                                        if (preg_match('/^[a-zA-Z0-9_]+$/', $fieldName)) {
                                            $validOrders[] = 'e.' . $field . ' ' . $direction;
                                        }
                                    } elseif (in_array($field, $allowedFields)) {
                                        $validOrders[] = 'a.' . $field . ' ' . $direction;
                                    }
                                }

                                if (!empty($validOrders)) {
                                    $order = implode(',', $validOrders) . ',a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.sorting ASC,a.date DESC,a.id DESC';
                                }
                            }
                    }
                    break;
                case 'filter':
                    if ($value) {
                        $filter = explode('|', $value);
                        if (count($filter) == 2) {
                            $filter_arr = explode(',', $filter[1]);
                            if ($filter[0] == 'title') {
                                $filter[0] = 'a.title';
                            }
                            foreach ($filter_arr as $value) {
                                if ($value) {
                                    if ($params['fuzzy']) {
                                        $filterWhere[] = [$filter[0], 'like', "%" . escape_string($value) . "%"];
                                    } else {
                                        $filterWhere[] = [$filter[0], '=', escape_string($value)];
                                    }
                                }
                            }
                        }
                    }

                    break;
                case 'tags':
                    if ($value) {
                        $tags_arr = explode(',', $value);
                        foreach ($tags_arr as $value) {
                            if ($value) {
                                if ($params['fuzzy']) {
                                    $tagWhere[] = ['a.tags', 'like', "%" . escape_string($value) . "%"];
                                } else {
                                    $tagWhere[] = ['a.tags', '=', escape_string($value)];
                                }
                            }
                        }
                    }
                    break;
                case 'page':
                    $page = $value;
                    // 开启分页后，如果没有传入num 那么默认的num改为12条
                    if ($page && !isset($params['num'])) {
                        $num = 12;
                    }
                    break;
                case 'start':
                    // 起始数校验
                    if (! is_numeric($value) || $value < 1) {
                        $start = 0;
                    }
                    break;
                case 'lfield':
                    $lfield = $value;
                    break;
            }
        }

        if ($lfield) {
            $lfield .= ',id,outlink,type,scode,sortfilename,filename,urlname'; // 附加必须字段
            $fields = explode(',', $lfield);
            $fields = array_unique($fields); // 去重

            // 白名单：从数据库动态获取允许的字段
            $allowedFields = self::getAllowedFields();

            foreach ($fields as $key => $value) {
                $value = trim($value);

                if (strpos($value, 'ext_') === 0) {
                    // 扩展字段：验证字段名格式
                    $extFieldName = substr($value, 4);
                    if (preg_match('/^[a-zA-Z0-9_]+$/', $extFieldName)) {
                        $ext_table = true;
                        $fields[$key] = 'e.' . $value;
                    } else {
                        unset($fields[$key]); // 非法字段移除
                    }
                } elseif ($value == 'sortname') {
                    $fields[$key] = 'b.name as sortname';
                } elseif ($value == 'sortfilename') {
                    $fields[$key] = 'b.filename as sortfilename';
                } elseif ($value == 'subsortname') {
                    $fields[$key] = 'c.name as subsortname';
                } elseif ($value == 'subfilename') {
                    $fields[$key] = 'c.filename as subfilename';
                } elseif ($value == 'type' || $value == 'urlname') {
                    $fields[$key] = 'd.' . $value;
                } elseif ($value == 'modelname') {
                    $fields[$key] = 'd.name as modelname';
                } elseif (in_array($value, $allowedFields)) {
                    $fields[$key] = 'a.' . $value;
                } else {
                    unset($fields[$key]); // 非白名单字段移除
                }
            }
        } else {
            $ext_table = true;
            $fields = array(
                'a.*',
                'b.name as sortname',
                'b.filename as sortfilename',
                'c.name as subsortname',
                'c.filename as subfilename',
                'd.type',
                'd.name as modelname',
                'd.urlname',
                'e.*',
                'f.gcode'
            );
        }

        $scode_arr = [];

        if ($scode == '*') {
            $scode = '';
        }

        if ($scode) {
            // 获取所有子类分类编码
            $arr = explode(',', $scode); // 传递有多个分类时进行遍历
            $arr = array_map('trim', $arr);
            $arr = array_filter($arr);
            $contentSortModel = new ContentSort();
            $scodes = $arr;
            foreach ($arr as $value) {
                $scodes = array_merge($scodes, $contentSortModel->getSubScodes($value));
            }
            $scodes = array_unique($scodes);
            $scode_arr = [
                ['a.scode', 'in', $scodes],
                ['a.subscode', 'find in set', implode(',', $scodes)]
            ];
            $where[] = function ($query) use ($scode_arr) {
                $query->whereOr($scode_arr);
            };
        }

        if ($lg) {
            $where[] = [
                'a.acode',
                '=',
                $lg
            ];
        }
        // 采取keyword方式
        if ($keyword) {
            if (strpos($field, '|')) { // 匹配多字段的关键字搜索
                $field = explode('|', $field);
                $keywordWhere = [];
                foreach ($field as $value) {
                    if ($value == 'title') {
                        $value = 'a.title';
                    }
                    if ($params['fuzzy']) {
                        $keywordWhere[] = [$value, 'like', '%' . $keyword . '%'];
                    } else {
                        $keywordWhere[] = [$value, 'like', $keyword];
                    }
                }
                $where[] = function ($query) use ($keywordWhere) {
                    $query->whereOr($keywordWhere);
                };
            } else { // 匹配单一字段的关键字搜索
                if ($field) {
                    if ($field == 'title') {
                        $field = 'a.title';
                    }
                } else {
                    $field = 'a.title';
                }
                if ($params['fuzzy']) {
                    $where[] = [$field, 'like', '%' . $keyword . '%'];
                } else {
                    $where[] = [$field, '=', $keyword];
                }
            }
        }

        /* 任意搜索字段 */
        /* 排除字段 */
        $exclude = ['page', 'start', 'lfield', 'keyword', 'fuzzy', 'scode', 'lg', 'searchtpl', 'field', 'num'];
        // 白名单：从数据库动态获取允许搜索的字段
        $allowedSearchFields = self::getAllowedFields();

        foreach (request()->param() as $key => $value) {
            if (in_array($key, $exclude)) {
                continue;
            }
            if (!!$value = request()->param($key)) {
                // 严格白名单验证
                if (in_array($key, $allowedSearchFields)) {
                    $fieldName = ($key == 'title') ? 'a.title' : 'a.' . $key;
                    if ($params['fuzzy']) {
                        $where[] = [$fieldName, 'like', '%' . $value . '%'];
                    } else {
                        $where[] = [$fieldName, '=', $value];
                    }
                }
            }
        }

        // 筛选条件支持模糊匹配
        $db = self::name('cms_content')
            ->alias('a')
            ->field($fields)
            ->where($where)
            ->whereOr($filterWhere)
            ->whereOr($tagWhere)
            ->join('cms_content_sort b', 'a.scode=b.scode', 'LEFT')
            ->join('cms_content_sort c', 'a.subscode=c.scode', 'LEFT')
            ->join('cms_model d', 'b.mcode=d.mcode', 'LEFT')
            ->join('user_level f', 'a.gid=f.id', 'LEFT');

        // 获取扩展字段表
        if ($ext_table) {
            $db->join('cms_content_ext e', 'a.id=e.contentid', 'LEFT');
        }

        $db->order($order);
        if ($page) {
            // 扩展字段数据筛选
            $ext_where = self::buildExtWhere(request()->get(), $params);
            $db->where($ext_where);

            $res = $db->paginate([
                'query' => request()->get(),
                'list_rows' => $num,
            ], $simple);

            if (!$res->isEmpty()) {
                $data['total'] = $res->total();
                $data['per_page'] = $res->listRows();
                $data['current_page'] = $res->currentPage();
                $data['last_page'] = $res->lastPage();
                $data['data'] = $res->getCollection()->toArray();
                $data['page'] = $res->pageData();
            }
        } else {
            $res = $db->limit($start, $num)->select();
            if (!$res->isEmpty()) {
                $data['total'] = $res->count();
                $data['data'] = $res->toArray();
            }
        }

        return $data;
    }
}

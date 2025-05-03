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

use think\facade\Db;
use think\Model;

class Content extends Model
{
    protected $name = "cms_content";

    protected $append = [
        'link'
    ];

    public function getContentAttr($value): string
    {
        /* 替换富文本中的图片域名 */
        $value = addEditorDomain($value, request()->domain());
        return !$value ? '' : html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        ;
    }

    public function getIcoAttr($value, $data)
    {
        return $value ? full_url($value) : '';
    }

    public function getPicsAttr($value, $data)
    {
        if ($value) {
            $pics = explode(',', $value);
            if (count($pics) == 1) {
                return full_url($pics[0]);
            }
            foreach ($pics as &$pic) {
                $pic = full_url($pic);
            }
            return $pics;
        }
        return [];
    }

    public function getLinkAttr($value, $data)
    {
        if (!isset($data['type']) || !isset($data['urlname']) || !isset($data['sortfilename'])) {
            return '';
        }
        if ($data['outlink']) {
            return $data['outlink'];
        }
        return bdurl($data['type'], $data['urlname'], 'content', $data['scode'], $data['sortfilename'], $data['id'], $data['filename']);
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
        if ($result) {
            $pics = $result['pics'];
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
                if ($num) {
                    // 限制标签数量
                    $tags = array_slice($tags, 0, $num);
                }
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
        // $target = 'tag';
        foreach ($data as $key => &$value) {
            $value['n'] = $key;
            $value['i'] = $key + 1;

            if ($target == 'tag') {
                $value['link'] = url('/tag/'.$value['tags']);
            } else {
                $value['link'] = bdurl($value['sort']['type'], $value['sort']['urlname'], 'list', $value['sort']['scode'], $value['sort']['filename'], '', '').'?tag=' . urlencode($value['tags']);
            }
            $value['text'] = $value['tags'];
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
            $where[] = function ($query) use ($scode) {
                $query->whereOr([
                    'a.scode' => $scode,
                    'b.filename' => $scode
                ]);
            };
        }
        if ($id) {
            $where[] = function ($query) use ($id) {
                $query->whereOr([
                    'a.id' => $id,
                    'a.filename' => $id
                ]);
            };
        }
        $result = self::alias('a')
            ->field($field)
            ->where($where)
            ->where('a.status', 1)
            ->join('cms_content_sort b', 'a.scode=b.scode', 'LEFT')
            ->join('cms_content_sort c', 'a.subscode=c.scode', 'LEFT')
            ->join('cms_model d', 'b.mcode=d.mcode', 'LEFT')
            ->join('cms_content_ext e', 'a.id=e.contentid', 'LEFT')
            ->join('cms_member_group f', 'a.gid=f.id', 'LEFT')
            ->order('id DESC')
            ->find();
        if (!$result) {
            return [];
        }
        $result['subsortlink'] = $result['subscode'] ? bdurl($result['type'], $result['urlname'], 'list', $result['subscode'], $result['subfilename'], '', '') : '';
        $result->inc('visits')->save();
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
            ['a.scode','in',$scodes],
            ['a.acode','=',get_frontend_lang()],
            ['a.status','=',1],
            ['a.date','<',date('Y-m-d H:i:s')],
        ];

        if ($type == 'next') {
            $order = 'a.id ASC';
            $where[] = ['a.id','>',$id];
            /* 暂无内容 */
            $data['nextcontent'] = "<a href='javascript:;'>".__('Not have')."</a>";
            $data['nextlink'] = "javascript:;";
            $data['nexttitle'] = __('Not have');
            $data['nextico'] = "";

        } else {
            $order = 'a.id DESC';
            $where[] = ['a.id','<',$id];
            /* 暂无内容 */
            $data['precontent'] = "<a href='javascript:;'>".__('Not have')."</a>";
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
            $data['nextcontent'] = "<a href='".$content['link']."'>".$content['title']."</a>";
            $data['nextlink'] = $content['link'];
            $data['nexttitle'] = $content['title'];
            $data['nextico'] = $content['ico'];
        } else {
            $data['precontent'] = "<a href='".$content['link']."'>".$content['title']."</a>";
            $data['prelink'] = $content['link'];
            $data['pretitle'] = $content['title'];
            $data['preico'] = $content['ico'];
        }

        return $data;
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
        $simple = false;//简洁分页
        $num = 16;//每页显示数量
        $page = false;
        $start = 0;
        $filterWhere = []; //筛选条件
        $tagWhere = [];
        $where = [
            ['a.status','=',1],
            ['d.type','=',2],
            ['a.date','<',date('Y-m-d H:i:s') ],
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
                            break;
                        default:
                            if ($value) {
                                $orders = explode(',', $value);
                                foreach ($orders as $k => $v) {
                                    if (strpos($v, 'ext_') === 0) {
                                        $orders[$k] = 'e.' . $v;
                                    } else {
                                        $orders[$k] = 'a.' . $v;
                                    }
                                }
                                $value = implode(',', $orders);
                                $order = $value . ',a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.sorting ASC,a.date DESC,a.id DESC';
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
                                        $filterWhere[] = [$filter[0],'like',"%".escape_string($value) . "%"];
                                    } else {
                                        $filterWhere[] = [$filter[0],'=',escape_string($value) ];
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
                                    $tagWhere[] = ['a.tags','like',"%".escape_string($value) . "%"];
                                } else {
                                    $tagWhere[] = ['a.tags','=',escape_string($value) ];
                                }
                            }
                        }
                    }
                    break;
                case 'ispics':
                    $where[] = ["a.pics",'<>',""];
                    break;
                case 'isico':
                    $where[] = ["a.ico",'<>',""];
                    break;
                case 'istop':
                    $where[] = ["a.istop",'=',$value];
                    break;
                case 'isrecommend':
                    $where[] = ["a.isrecommend",'=',$value];
                    break;
                case 'isheadline':
                    $where[] = ["a.isheadline",'=',$value];
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

        if ($lfield) {
            $lfield .= ',id,outlink,type,scode,sortfilename,filename,urlname'; // 附加必须字段
            $fields = explode(',', $lfield);
            $fields = array_unique($fields); // 去重
            foreach ($fields as $key => $value) {
                if (strpos($value, 'ext_') === 0) {
                    $ext_table = true;
                    $fields[$key] = 'e.' . $value;
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
                } else {
                    $fields[$key] = 'a.' . $value;
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
            $contentSortModel = new ContentSort();
            foreach ($arr as $value) {
                $scodes = $contentSortModel->getSubScodes(trim($value));
            }

            $scode_arr = [
                ['a.scode','in',$scodes],
                ['a.subscode','=',$scode]
            ];
            $where[] = function ($query) use ($scode_arr) {
                $query->whereOr($scode_arr);
            };
        }

        if ($lg) {
            $where[] = [
                'a.acode','=',$lg
            ];
        }
        if ($page) {
            $tag = request()->param('tag');
            if ($tag) {
                if ($params['fuzzy']) {
                    $tagWhere[] = ['a.tags','like',"%".escape_string($tag) . "%"];
                } else {
                    $tagWhere[] = ['a.tags','=',escape_string($tag) ];
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
            ->join('cms_member_group f', 'a.gid=f.id', 'LEFT');

        // 获取扩展字段表
        if ($ext_table) {
            $db->join('cms_content_ext e', 'a.id=e.contentid', 'LEFT');
        }
        $db->order($order);

        if ($page) {
            // 扩展字段数据筛选
            $get = request()->get();
            $ext_where = [];
            foreach ($get as $key => $value) {
                if (preg_match('/^ext_[\w\-]+$/', $key)) { // 其他字段不加入
                    if ($params['fuzzy']) {
                        $ext_where[] = [$key,'like','%'.$value.'%'];
                    } else {
                        $ext_where[$key] = $value;
                    }
                }
            }
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
            } else {
                $data['total'] = $res->total();
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
            ['a.status','=',1],
            ['c.type','=',2],
            ['a.date','<',date('Y-m-d H:i:s')],
            ['a.scode','=',$scode],
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
        $simple = false;//简洁分页
        $num = 16;//每页显示数量
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
            ['a.status','=',1],
            ['d.type','=',2],
            ['a.date','<',date('Y-m-d H:i:s') ],
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
                                $orders = explode(',', $value);
                                foreach ($orders as $k => $v) {
                                    if (strpos($v, 'ext_') === 0) {
                                        $orders[$k] = 'e.' . $v;
                                    } else {
                                        $orders[$k] = 'a.' . $v;
                                    }
                                }
                                $value = implode(',', $orders);
                                $order = $value . ',a.istop DESC,a.isrecommend DESC,a.isheadline DESC,a.sorting ASC,a.date DESC,a.id DESC';
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
                                        $filterWhere[] = [$filter[0],'like',"%".escape_string($value) . "%"];
                                    } else {
                                        $filterWhere[] = [$filter[0],'=',escape_string($value) ];
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
                                    $tagWhere[] = ['a.tags','like',"%".escape_string($value) . "%"];
                                } else {
                                    $tagWhere[] = ['a.tags','=',escape_string($value) ];
                                }
                            }
                        }
                    }
                    break;
                case 'page':
                    $page = $value;
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
            foreach ($fields as $key => $value) {
                if (strpos($value, 'ext_') === 0) {
                    $ext_table = true;
                    $fields[$key] = 'e.' . $value;
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
                } else {
                    $fields[$key] = 'a.' . $value;
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
            $contentSortModel = new ContentSort();
            foreach ($arr as $value) {
                $scodes = $contentSortModel->getSubScodes(trim($value));
            }

            $scode_arr = [
                ['a.scode','in',$scodes],
                ['a.subscode','=',$scode]
            ];
            $where[] = function ($query) use ($scode_arr) {
                $query->whereOr($scode_arr);
            };
        }

        if ($lg) {
            $where[] = [
                'a.acode','=',$lg
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
                        $keywordWhere[] = [$value,'like','%' . $keyword . '%'];
                    } else {
                        $keywordWhere[] = [$value,'like', $keyword ];
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
                    $where[] = [$field,'like','%' . $keyword . '%'];
                } else {
                    $where[] = [$field,'=', $keyword ];
                }
            }
        }

        /* 任意搜索字段 */
        /* 排除字段 */
        $exclude = ['page','start','lfield','keyword','fuzzy','scode','lg','searchtpl','field','num'];
        foreach (request()->param() as $key => $value) {
            if (in_array($key, $exclude)) {
                continue;
            }
            if (!!$value = request()->param($key)) {
                if ($key == 'title') {
                    $key = 'a.title';
                }
                if (preg_match('/^[\w\-\.]+$/', $key)) { // 带有违规字符时不带入查询
                    $where[] = [$key,'=',$value];
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
            ->join('cms_member_group f', 'a.gid=f.id', 'LEFT');

        // 获取扩展字段表
        if ($ext_table) {
            $db->join('cms_content_ext e', 'a.id=e.contentid', 'LEFT');
        }
        $db->order($order);
        if ($page) {
            // 扩展字段数据筛选
            $get = request()->get();
            $ext_where = [];
            foreach ($get as $key => $value) {
                if (preg_match('/^ext_[\w\-]+$/', $key)) { // 其他字段不加入
                    if ($params['fuzzy']) {
                        $ext_where[] = [$key,'like','%'.$value.'%'];
                    } else {
                        $ext_where[$key] = $value;
                    }
                }
            }
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

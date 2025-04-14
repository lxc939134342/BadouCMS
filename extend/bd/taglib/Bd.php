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

namespace bd\taglib;

use ba\Random;
use think\Exception;
use think\template\TagLib;

class Bd extends TagLib
{
    protected $tags = [
        'sort'       => ['attr' => 'cid', 'close' => 1],
        'nav'        => ['attr' => '', 'close' => 1],
        'list'       => ['attr' => '','close' => 1],
        'slide'      => ['attr' => 'gid', 'close' => 1],
        'link'       => ['attr' => 'gid', 'close' => 1],
        'content'    => ['attr' => '','close' => 1],
        'position'   => ['attr' => '','close' => 0],
        'select'     => ['attr' => 'field','close' => 1],
        'selectall'  => ['attr' => 'field','close' => 0],
        'search'     => ['attr' => '','close' => 1],
        'comment'    => ['attr' => '','close' => 1],
        'commentsub' => ['attr' => '','close' => 1],
        'message'    => ['attr' => '','close' => 1],
        'form'       => ['attr' => 'fcode','close' => 0],
        'formlist'   => ['attr' => 'fcode','close' => 1],
        'tags'       => ['attr' => '','close' => 1],
        'pics' => ['attr' => '','close' => 1],
        'qrcode' => ['attr' => 'string','close' => 0],
        'loop' => ['attr' => '','close' => 1],
    ];

    /*当前分类 子分类列表*/
    public function tagSort($tag, $content)
    {
        if (!isset($tag['scode'])) {
            throw new \think\Exception('scode参数不能为空');
        }
        $this->autoBuildVar($tag['scode']);
        $scode  = $this->isVar($tag['scode']) ? $tag['scode'] : '"'.$tag['scode'].'"';

        $alias  = $tag['alias'] ?? 'sort';
        $empty  = $tag['empty'] ?? '';
        $key    = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod    = $tag['mod'] ?? '2';
        $var    = Random::build('alnum', 10);
        $parse  = '<?php ';
        $parse .= '$__' . $var . '__ = \app\index\model\cms\ContentSort::getMultSort(' . $scode . ');';
        $parse .= '?>';
        $parse .= '{volist name="$__' . $var . '__" id="' .$alias . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '<?php unset($'.$alias.');$sort=$listsort; ?>';
        return $parse;
    }

    /* 导航 */
    public function tagNav($tag, $content): string
    {
        $parent  = $tag['parent'] ?? 0;
        $num     = $tag['num'] ?? false;
        $scode   = $tag['scode'] ?? false;
        $alias   = $tag['alias'] ?? 'nav';
        $empty   = $tag['empty'] ?? '';
        $key     = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod     = $tag['mod'] ?? '2';
        if ($parent) {
            $parent = $this->autoBuildVar($parent);
        }
        if ($scode) {
            $scode = $this->autoBuildVar($scode);
        }
        $var     = Random::build('alnum', 10);
        $parse   = '<?php ';
        $parse  .= '$__' . $var . '__ = \app\index\model\cms\ContentSort::navList(' . $parent . ',"' . $scode . '","' . $num . '");';
        $parse  .= ' ?>';
        $parse  .= '{volist name="$__' . $var . '__" id="' . $alias . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= '<?php $'.$alias.'["'.$key.'"]=$'.$key.';?>';
        $parse  .= $content;
        $parse  .= '{/volist}';
        $parse  .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    /* 内容列表 */
    public function tagList($tag, $content): string
    {
        $num   = $tag['num'] ?? 15;
        $alias    = $tag['alias'] ?? 'list';
        $empty = $tag['empty'] ?? '';
        $key   = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod   = $tag['mod'] ?? '2';
        $scode = $tag['scode'] ?? null;
        $page  = $tag['page'] ?? 'false';
        $start  = $tag['start'] ?? '0';
        $filter = $tag['filter'] ?? null;
        $tags = $tag['tags'] ?? null;
        $isico = $tag['isico'] ?? '0';
        $ispics = $tag['ispics'] ?? '0';
        $istop = $tag['istop'] ?? '0';
        $isrecommend = $tag['isrecommend'] ?? '0';
        $isheadline = $tag['isheadline'] ?? '0';
        $fuzzy = $tag['fuzzy'] ?? true;

        $params = [
            "'num'=>{$num}",
            "'page'=>{$page}",
            "'start'=>{$start}",
            "'fuzzy'=>{$fuzzy}",
        ];
        if ($scode == '*') {
            $params[] = '"scode"=>"*"';
        } elseif ($scode) {
            $params[] = '"scode"=>'.$scode;
        } else {
            $params[] = '"scode"=>$sort["scode"]';
            if ($page == 'false') {
                $params[] = '"page"=>1';
            }
        }

        $tags ? $params[] = '"tags"=>"'.$tags.'"' : '';
        $filter ? $params[] = '"filter"=>'.$filter : '';
        $isico ? $params[] = '"isico"=>'.$isico : '';
        $ispics ? $params[] = '"ispics"=>'.$ispics : '';
        $istop ? $params[] = '"istop"=>'.$istop : '';
        $isrecommend ? $params[] = '"isrecommend"=>'.$isrecommend : '';
        $isheadline ? $params[] = '"isheadline"=>'.$isheadline : '';

        $var     = Random::build('alnum', 10);
        $parse   = '<?php ';
        $parse  .= '$__' . $var . '__ = \app\index\model\cms\Content::contentList([' . implode(',', $params) . ']);';
        $parse  .= '$__' . $var . '__data__ = $__' . $var . '__["data"];';
        $parse  .= '$page = $__' . $var . '__["page"];';
        $parse  .= ' ?>';
        $parse  .= '{volist name="$__' . $var . '__data__" id="' . $alias . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= '<?php $'.$alias.'["'.$key.'"]=$'.$key.';?>';
        $parse  .= $content;
        $parse  .= '{/volist}';
        return $parse;
    }

    /* 指定内容 */
    public function tagContent($tag, $content): string
    {
        $id    = $tag['id'] ?? null ;
        $scode = $tag['scode'] ?? null;
        $scode ? $param[] = '"'. $scode .'"' : '';
        $id ? $param[] = '"'. $id .'"' : '';
        $empty = $tag['empty'] ?? '';
        $alias    = $tag['alias'] ?? 'content';
        $parse   = '<?php ';
        $parse  .= '$' . $alias . ' = \app\index\model\cms\Content::getContent('.implode(',', $param).');';
        $parse  .= ' ?>';
        $parse .= '{if $' .$alias . '}';
        $parse .= $content;
        $parse .= '{else /}';
        $parse .= '<?php echo "' . $empty . '" ;?>';
        $parse .= '{/if}';
        return $parse;
    }

    /* 轮播图 */
    public function tagSlide($tag, $content): string
    {
        $gid = $tag['gid'] ?? 0;
        $num = $tag['num'] ?? 5;
        $alias      = $tag['alias'] ?? 'slide';
        $empty   = $tag['empty'] ?? '';
        $key     = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod     = $tag['mod'] ?? '2';
        $var     = Random::build('alnum', 10);
        $parse   = '<?php ';
        $parse  .= '$__' . $var . '__ = \app\index\model\cms\Slide::slideList('.$gid.',"'.$num.'");';
        $parse  .= ' ?>';
        $parse  .= '{volist name="$__' . $var . '__" id="' . $alias . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse  .= $content;
        $parse  .= '{/volist}';
        return $parse;
    }

    /* 友情链接 */
    public function tagLink($tag, $content): string
    {
        $gid = $tag['gid'] ?? 0;
        $num = $tag['num'] ?? 10;
        $alias      = $tag['alias'] ?? 'link';
        $empty   = $tag['empty'] ?? '';
        $key     = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod     = $tag['mod'] ?? '2';
        $var     = Random::build('alnum', 10);
        $parse   = '<?php ';
        $parse  .= '$__' . $var . '__ = \app\index\model\cms\Link::linkList('.$gid.',"'.$num.'");';
        $parse  .= ' ?>';
        $parse  .= '{volist name="$__' . $var . '__" id="' . $alias . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse  .= $content;
        $parse  .= '{/volist}';
        return $parse;
    }

    /* 面包屑 */
    public function tagPosition($tag, $content): string
    {
        $separator = $tag['separator'] ?? '>>';
        $separatoricon = $tag['separatoricon'] ?? '';
        $indextext = $tag['indextext'] ?? null;
        $indexicon = $tag['indexicon'] ?? '';
        $params = [
            "'scode'=>\$sort['scode']",
            "'separator'=>'{$separator}'",
            "'separatoricon'=>'{$separatoricon}'",
            "'indextext'=>'{$indextext}'",
            "'indexicon'=>'{$indexicon}'",
        ];

        $parse   = '<?php ';
        $parse  .= 'echo \app\index\model\cms\ContentSort::positionHtml(['.implode(',', $params).']);';
        $parse  .= ' ?>';
        return $parse;
    }

    /* 多条件筛选 */
    public function tagSelect($tag, $content): string
    {
        $field  = $tag['field'];
        $alias  = $tag['alias'] ?? 'select';
        $empty  = $tag['empty'] ?? '';
        $key    = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod    = $tag['mod'] ?? '2';
        $var    = Random::build('alnum', 10);
        $parse  = '<?php ';
        $parse .= '$__' . $var . '__ = \app\index\model\cms\Extfield::getSelect("'.$field.'");';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $alias . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    /* 多条件筛选的  全部 */
    public function tagSelectall($tag, $content): string
    {
        $field  = $tag['field'];
        $text   = $tag['text'] ?? '';
        $class  = $tag['class'] ?? '';
        $active = $tag['active'] ?? '';

        $params = [
            "'field'=>'{$field}'",
            "'text'=>'{$text}'",
            "'class'=>'{$class}'",
            "'active'=>'{$active}'",
        ];
        $parse   = '<?php ';
        $parse  .= 'echo \app\index\model\cms\Extfield::getSelectAllLabel(['.implode(',', $params).']);';
        $parse  .= ' ?>';
        return $parse;
    }

    /**
     * 搜索列表
     * @param mixed $tag
     * @param mixed $content
     * @return string
     */
    public function tagSearch($tag, $content): string
    {
        $num   = $tag['num'] ?? 15;
        $alias    = $tag['alias'] ?? 'search';
        $empty = $tag['empty'] ?? __('No Data');
        $key   = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod   = $tag['mod'] ?? '2';
        $page  = $tag['page'] ?? 'false';
        $start  = $tag['start'] ?? '0';
        $filter = $tag['filter'] ?? null;
        $tags = $tag['tags'] ?? null;
        $fuzzy = $tag['fuzzy'] ?? true;

        $params = [
            "'num'=>{$num}",
            "'page'=>{$page}",
            "'start'=>{$start}",
            "'fuzzy'=>{$fuzzy}",
        ];

        $tags ? $params[] = '"tags"=>'.$tags : '';
        $filter ? $params[] = '"filter"=>'.$filter : '';

        $var     = Random::build('alnum', 10);
        $parse   = '<?php ';
        $parse  .= '$__' . $var . '__ = \app\index\model\cms\Content::searchList([' . implode(',', $params) . ']);';
        $parse  .= '$__' . $var . '__data__ = $__' . $var . '__["data"];';
        $parse  .= '$page = $__' . $var . '__["page"];';
        $parse  .= ' ?>';
        $parse  .= '{volist name="$__' . $var . '__data__" id="' . $alias . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse  .= $content;
        $parse  .= '{/volist}';
        return $parse;
    }

    //文章评论
    public function tagComment($tag, $content): string
    {
        $contentid = $tag['contentid'];
        $alias    = $tag['alias'] ?? 'comment';
        $empty = $tag['empty'] ?? __('No Data');
        $key   = !empty($tag['key']) ? $tag['key'] : 'i';
        $num   = $tag['num'] ?? 15;
        $mod   = $tag['mod'] ?? '2';
        $page  = $tag['page'] ?? 'true';
        $start  = $tag['start'] ?? '0';
        $pid    = $tag['pid'] ?? '0';
        $order  = $tag['order'] ?? 'a.id desc';

        $params = [
            "{$contentid}",
            "{$pid}",
            "{$num}",
            "'{$order}'",
            "{$page}",
            "{$start}",
        ];

        $var     = Random::build('alnum', 10);
        $parse   = '<?php ';
        $parse  .= '$__' . $var . '__ = (new \app\index\model\cms\MemberComment())->getComment(' . implode(',', $params) . ');';
        $parse  .= '$__' . $var . '__data__ = $__' . $var . '__["data"];';
        $parse  .= '$page = $__' . $var . '__["page"];';
        $parse  .= '?>';
        $parse  .= '{volist name="$__' . $var . '__data__" id="' . $alias . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse  .= $content;
        $parse  .= '{/volist}';
        return $parse;
    }
    /* 文章评论子评论 */
    public function tagCommentsub($tag, $content): string
    {
        $contentid = $tag['contentid'] ?? '$content["id"]';
        $alias    = $tag['alias'] ?? 'commentsub';
        $key   = !empty($tag['key']) ? $tag['key'] : 'i';
        $num   = $tag['num'] ?? 15;
        $mod   = $tag['mod'] ?? '2';
        $start  = $tag['start'] ?? '0';
        $pid    = $tag['pid'] ?? '$comment["id"]';
        $order  = $tag['order'] ?? 'a.id desc';

        $params = [
            "{$contentid}",
            "{$pid}",
            "{$num}",
            "'{$order}'",
            "false",
            "{$start}",
        ];

        $var     = Random::build('alnum', 10);
        $parse   = '<?php ';
        $parse  .= '$__' . $var . '__ = (new \app\index\model\cms\MemberComment())->getComment(' . implode(',', $params) . ');';
        $parse  .= '$__' . $var . '__data__ = $__' . $var . '__["data"];';
        $parse  .= '?>';
        $parse  .= '{volist name="$__' . $var . '__data__" id="' . $alias . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse  .= $content;
        $parse  .= '{/volist}';
        return $parse;
    }

    /**
     * 留言列表
     * @param mixed $tag
     * @param mixed $content
     * @return string
     */
    public function tagMessage($tag, $content): string
    {
        $alias = $tag['alias'] ?? 'message';
        $empty = $tag['empty'] ?? __('No Data');
        $key   = !empty($tag['key']) ? $tag['key'] : 'i';
        $num   = $tag['num'] ?? 15;
        $mod   = $tag['mod'] ?? '2';
        $page  = $tag['page'] ?? 'true';
        $start = $tag['start'] ?? '0';
        $order = $tag['order'] ?? 'a.id desc';

        $params = [
            "{$num}",
            "'{$order}'",
            "{$page}",
            "{$start}",
        ];

        $var     = Random::build('alnum', 10);
        $parse   = '<?php ';
        $parse  .= '$__' . $var . '__ = (new \app\index\model\cms\Message())->getList(' . implode(',', $params) . ');';
        $parse  .= '$__' . $var . '__data__ = $__' . $var . '__["data"];';
        $parse  .= '$page = $__' . $var . '__["page"];';
        $parse  .= '?>';
        $parse  .= '{volist name="$__' . $var . '__data__" id="' . $alias . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse  .= $content;
        $parse  .= '{/volist}';
        return $parse;
    }

    /**
     * 自定义表单链接
     * @param mixed $tag
     * @param mixed $content
     * @return string
     */
    public function tagForm($tag, $content): string
    {
        $fcode = $tag['fcode'];
        $parse   = '<?php ';
        $parse  .= 'echo (new \app\index\model\cms\Form())->getFormLink('.$fcode.');';
        $parse  .= ' ?>';
        return $parse;
    }

    /**
     * 表单列表
     * @param mixed $tag
     * @param mixed $content
     * @return string
     */
    public function tagFormlist($tag, $content): string
    {
        $fcode = $tag['fcode'];
        $alias    = $tag['alias'] ?? 'form';
        $empty = $tag['empty'] ?? __('No Data');
        $key   = !empty($tag['key']) ? $tag['key'] : 'i';
        $num   = $tag['num'] ?? 15;
        $mod   = $tag['mod'] ?? '2';
        $page  = $tag['page'] ?? 'true';
        $start  = $tag['start'] ?? '0';
        $order  = $tag['order'] ?? 'id desc';

        $params = [
            "{$fcode}",
            "{$num}",
            "'{$order}'",
            "{$page}",
            "{$start}",
        ];

        $var     = Random::build('alnum', 10);
        $parse   = '<?php ';
        $parse  .= '$__' . $var . '__ = (new \app\index\model\cms\Form())->getFormList(' . implode(',', $params) . ');';
        $parse  .= '$__' . $var . '__data__ = $__' . $var . '__["data"];';
        $parse  .= '$page = $__' . $var . '__["page"];';
        $parse  .= '?>';
        $parse  .= '{volist name="$__' . $var . '__data__" id="' . $alias . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse  .= $content;
        $parse  .= '{/volist}';
        return $parse;
    }

    public function tagTags($tag, $content): string
    {
        $id = $tag['id'] ?? '""';
        $scode = $tag['scode'] ?? '""';
        $alias    = $tag['alias'] ?? 'tags';
        $empty = $tag['empty'] ?? '';
        $key   = !empty($tag['key']) ? $tag['key'] : 'i';
        $num   = $tag['num'] ?? 0;
        $mod   = $tag['mod'] ?? '2';
        $var     = Random::build('alnum', 10);
        $parse   = '<?php ';
        $parse  .= '$__' . $var . '__ = \app\index\model\cms\Content::getContentTags('.$id.','.$scode.','.$num.');';
        $parse  .= '?>';
        $parse  .= '{volist name="$__' . $var . '__" id="' . $alias . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse  .= $content;
        $parse  .= '{/volist}';
        return $parse;
    }

    public function tagPics($tag, $content): string
    {
        $id = $tag['id'] ?? '""';
        $field = $tag['field'] ?? '"pics"';
        $alias    = $tag['alias'] ?? 'pics';
        $empty = $tag['empty'] ?? '';
        $key   = !empty($tag['key']) ? $tag['key'] : 'i';
        $num   = $tag['num'] ?? 0;
        $mod   = $tag['mod'] ?? '2';
        $var     = Random::build('alnum', 10);
        $parse   = '<?php ';
        $parse  .= '$__' . $var . '__ = (new \app\index\model\cms\Content)->getContentPics('.$id.','.$field.','.$num.');';
        $parse  .= '?>';
        $parse  .= '{volist name="$__' . $var . '__" id="' . $alias . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse  .= $content;
        $parse  .= '{/volist}';
        return $parse;
    }

    public function tagQrcode($tag, $content): string
    {
        $string = $tag['string'] ?? '""';
        $string = $this->autoBuildVar($string);
        $parse   = '<?php echo \'<img src="'.request()->domain(true). '/api/cms.qrcode/index?string=\'.'.$string.'.\'" class="qrcode" alt="二维码">\'; ?>';
        return $parse;
    }

    /**
     * 循环标签
     * @return string
     */
    public function tagLoop($tag, $content): string
    {
        $start = $tag['start'] ?? 0;
        $end = $tag['end'] ?? 0;
        $parse = '<?php ';
        $parse .= '$loop = ["i"=>0, "index"=>1];';
        $parse .= 'for($i='.$start.'; $i<'.$end.'; $i++) {';
        $parse .= '$loop["i"] = $i;';
        $parse .= '$loop["index"] = $i+1;';
        $parse .= '?>';
        $parse .= $content;
        $parse .= '<?php } ?>';
        return $parse;
    }

    protected function isVar(string $name): bool
    {
        // 判断是否是变量
        $flag = substr($name, 0, 1);
        if ($flag == '$' || $flag == ':') {
            return true;
        }
        return false;
    }


    public function autoBuildVar(string &$name): string
    {
        // 处理字符串
        if (preg_match("/^('|\")(.*)('|\")\$/i", $name, $matches)) {
            $quote = $matches[1] == '"' ? "'" : '"';
            $name  = $quote . $matches[2] . $quote;
            return $name;
        }

        $flag = substr($name, 0, 1);
        if (':' == $flag) {
            // 以:开头为函数调用，解析前去掉:
            $name = substr($name, 1);
        }

        // 常量不需要解析
        if (defined($name)) {
            return $name;
        }
        $default_filter = $this->tpl->getConfig('default_filter');
        $this->tpl->config(['default_filter' => '']);
        $this->tpl->parseVar($name);
        $this->tpl->parseVarFunction($name);
        $this->tpl->config(['default_filter' => $default_filter]);
        return $name;
    }
}

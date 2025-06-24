# CMS 模块开发文档

## 模块概述

CMS 模块（八斗 CMS 内容管理系统）是一个基于 ThinkPHP 8 的内容管理系统。它提供了内容分类、内容列表、单页、轮播图、友情链接、评论、留言、自定义表单、标签等功能。

-   **模块名称**: cms
-   **标题**: 八斗 CMS 内容管理系统
-   **简介**: 基于 tp8 的内容管理系统
-   **作者**: lande
-   **官网**: http://www.badoucms.com
-   **版本**: 1.0.0
-   **状态**: 0 (表示模块可能处于禁用或未安装状态)

## 模块结构

```
modules/
└── cms/
    ├── .modulerc
    ├── Cms.php
    ├── common.php
    ├── info.ini
    ├── app/
    │   ├── admin/
    │   │   ├── controller/cms/
    │   │   │   ├── Area.php
    │   │   │   ├── Base.php
    │   │   │   ├── Company.php
    │   │   │   ├── Content.php
    │   │   │   ├── ContentSort.php
    │   │   │   ├── Extfield.php
    │   │   │   ├── Form.php
    │   │   │   ├── FormData.php
    │   │   │   ├── FormField.php
    │   │   │   ├── Label.php
    │   │   │   ├── Link.php
    │   │   │   ├── MemberComment.php
    │   │   │   ├── Message.php
    │   │   │   ├── Models.php
    │   │   │   ├── Single.php
    │   │   │   ├── Site.php
    │   │   │   ├── Slide.php
    │   │   │   └── Tags.php
    │   │   ├── lang/zh-cn/cms/
    │   │   │   ├── area.php
    │   │   │   ├── content.php
    │   │   │   ├── contentsort.php
    │   │   │   ├── extfield.php
    │   │   │   ├── form.php
    │   │   │   ├── formfield.php
    │   │   │   ├── label.php
    │   │   │   ├── link.php
    │   │   │   ├── membercomment.php
    │   │   │   ├── message.php
    │   │   │   ├── models.php
    │   │   │   ├── single.php
    │   │   │   ├── slide.php
    │   │   │   └── tags.php
    │   │   └── view/cms/
    │   │       ├── area/
    │   │       │   ├── add.html
    │   │       │   ├── edit.html
    │   │       │   └── index.html
    │   │       ├── common/builder/
    │   │       │   └── fields.html
    │   │       ├── company/
    │   │       │   └── index.html
    │   │       ├── content/
    │   │       │   ├── add.html
    │   │       │   ├── copy.html
    │   │       │   ├── edit.html
    │   │       │   ├── index.html
    │   │       │   └── move.html
    │   │       ├── contentsort/
    │   │       │   ├── add.html
    │   │       │   ├── batch_add.html
    │   │       │   ├── edit.html
    │   │       │   └── index.html
    │   │       ├── extfield/
    │   │       │   ├── add.html
    │   │       │   ├── edit.html
    │   │       │   └── index.html
    │   │       ├── form/
    │   │       │   ├── add.html
    │   │       │   ├── edit.html
    │   │       │   └── index.html
    │   │       ├── formdata/
    │   │       │   └── index.html
    │   │       ├── formfield/
    │   │       │   ├── add.html
    │   │       │   ├── edit.html
    │   │       │   └── index.html
    │   │       ├── label/
    │   │       │   ├── add.html
    │   │       │   ├── content.html
    │   │       │   └── edit.html
    │   │       ├── link/
    │   │       │   ├── add.html
    │   │       │   ├── edit.html
    │   │       │   └── index.html
    │   │       ├── membercomment/
    │   │       │   ├── index.html
    │   │       │   └── info.html
    │   │       ├── message/
    │   │       │   ├── edit.html
    │   │       │   └── index.html
    │   │       ├── models/
    │   │       │   ├── add.html
    │   │       │   ├── edit.html
    │   │       │   └── index.html
    │   │       ├── single/
    │   │       │   ├── edit.html
    │   │       │   └── index.html
    │   │       ├── site/
    │   │       │   └── index.html
    │   │       ├── slide/
    │   │       │   ├── add.html
    │   │       │   ├── edit.html
    │   │       │   └── index.html
    │   │       └── tags/
    │   │           ├── add.html
    │   │           ├── edit.html
    │   │           └── index.html
    │   ├── index/
    │   │   ├── controller/cms/
    │   │   │   ├── Account.php
    │   │   │   ├── Base.php
    │   │   │   ├── Comment.php
    │   │   │   ├── Detail.php
    │   │   │   ├── Index.php
    │   │   │   ├── Lists.php
    │   │   │   ├── Message.php
    │   │   │   ├── Search.php
    │   │   │   └── Sitemap.php
    │   │   ├── model/cms/
    │   │   │   ├── Company.php
    │   │   │   ├── Content.php
    │   │   │   ├── ContentSort.php
    │   │   │   ├── Extfield.php
    │   │   │   ├── Form.php
    │   │   │   ├── FormField.php
    │   │   │   ├── Label.php
    │   │   │   ├── Link.php
    │   │   │   ├── MemberComment.php
    │   │   │   ├── Message.php
    │   │   │   ├── Site.php
    │   │   │   ├── Slide.php
    │   │   │   ├── Tags.php
    │   │   │   └── User.php
    │   │   └── route/
    │   │       └── cms.php
    ├── library/
    │   └── Bootstrap.php
    ├── public/
    │   ├── modules/cms/js/
    │   │   └── change_lang.js
    │   └── template/cms/default/
    │       ├── bootstrap/
    │       │   ├── bootstrap.min.css
    │       │   └── bootstrap.min.js
    │       ├── css/
    │       │   ├── font-awesome.min.css
    │       │   └── style.css
    │       ├── fonts/
    │       │   ├── fontawesome-webfont.eot
    │       │   ├── fontawesome-webfont.svg
    │       │   ├── fontawesome-webfont.ttf
    │       │   ├── fontawesome-webfont.woff
    │       │   └── fontawesome-webfont.woff2
    │       ├── js/
    │       │   ├── function.js
    │       │   ├── jquery-2.2.4.min.js
    │       │   ├── jquery.bxslider.js
    │       │   └── lib.js
    │       └── comm/
    │           ├── comment.html
    │           ├── foot.html
    │           ├── head.html
    │           ├── nav.html
    │       ├── about.html
    │       ├── base.html
    │       ├── case.html
    │       ├── caselist.html
    │       ├── index.html
    │       ├── job.html
    │       ├── joblist.html
    │       ├── message.html
    │       ├── message2.html
    │       ├── news.html
    │       ├── newslist.html
    │       ├── product.html
    │       ├── productlist.html
    │       ├── search.html
    │       ├── tag.html
    │       └── user/
    │           └── userinfo.html
    ├── taglib/
    │   └── Bd.php
    └── template/
        └── cms/
            └── default/
                ├── about.html
                ├── base.html
                ├── case.html
                ├── caselist.html
                ├── index.html
                ├── job.html
                ├── joblist.html
                ├── message.html
                ├── message2.html
                ├── news.html
                ├── newslist.html
                ├── product.html
                ├── productlist.html
                ├── search.html
                ├── tag.html
                └── comm/
                    ├── comment.html
                    ├── foot.html
                    ├── head.html
                    ├── nav.html
                └── user/
                    └── userinfo.html
```

## 核心文件说明

### 1. `info.ini`

模块的基本信息配置文件，包含模块的名称、标题、简介、作者、官网、版本和状态。

```ini
name = cms
title = 八斗CMS内容管理系统
intro = 基于tp8的内容管理系统
author = lande
website = http://www.badoucms.com
version = 1.0.0
state = 0
```

**注意**: `state = 0` 表示模块可能处于禁用或未安装状态。

### 2. `Cms.php`

模块的入口类，定义了模块的生命周期方法。

-   **`AppInit()`**: 应用初始化时调用，主要用于引入 `common.php` 和绑定 ThinkPHP 的分页器到自定义的 `Bootstrap` 类。
    -   引入 `common.php`：`include_once __DIR__ . '/common.php';`
    -   绑定分页器：`bind('think\Paginator', 'modules\cms\library\Bootstrap');`
-   **`install()`**: 模块安装时调用，目前只返回 `true`。
-   **`uninstall()`**: 模块卸载时调用，目前只返回 `true`。

### 3. `common.php`

该文件包含了一系列辅助函数，用于提供各种常用功能，例如：

-   **用户及环境信息获取**:
    -   `get_user_bs($bs = null)`: 获取用户浏览器类型。
    -   `get_user_os($osstr = null)`: 获取用户操作系统类型。
    -   `get_user_ip()`: 获取用户 IP 地址。
-   **时间日期处理**:
    -   `get_datetime($timestamp = null)`: 格式化时间戳为日期时间。
    -   `get_date($timestamp = null)`: 格式化时间戳为日期。
    -   `get_date_diff($startstamp, $endstamp, $return = 'm')`: 计算时间戳差值（年、月、日）。
    -   `get_month_days($date, $start = 0, $interval = 1, $retamp = false)`: 获取间隔月份的起始及结束日期。
    -   `style($string, $style)`: 格式化时间戳或日期字符串。
-   **字符串处理**:
    -   `get_strpos($string, $find, $n)`: 获取字符串第 N 次出现位置。
    -   `escape_string($string)`: 转义字符串、数组、对象中的 HTML 实体和斜杠。
    -   `decode_string($string)`: 反转义 HTML 实体和斜杠。
    -   `decode_slashes($string)`: 反转义斜杠。
    -   `encrypt_string($string)`: 字符串双层 MD5 加密。
    -   `clear_html_blank($string)`: 清洗 HTML 代码中的空白符号。
    -   `trim_slash($string)`: 去除字符串两端斜线。
    -   `len($string, $length)`: 截取字符串。
    -   `lencn($string, $length)`: 截取中英混合字符串，中文算两个字符。
    -   `dropblank($string)`: 清除 HTML 空白符号。
    -   `bd_substr($string, $strat, $length)`: 截取字符串并清除 HTML 标签。
    -   `drophtml($str)`: 清除 HTML 标签与换行。
    -   `substr_both($string, $strat, $length)`: 中英混合字符串截取。
    -   `strlen_both($string)`: 中英混合字符串长度。
    -   `preg_replace_r($search, $replace, $subject)`: 递归替换字符串。
    -   `get_auto_code($string, $start = '1')`: 获取字符串型自动编码。
-   **数据结构转换与操作**:
    -   `object_to_array($object)`: 转换对象为数组。
    -   `array_to_object($array)`: 转换数组为对象。
    -   `in_object($needle, $object)`: 判断值是否在对象中。
    -   `result_value_search($needle, $result, $skey)`: 在结果集中查找指定字段父节点是否存在。
    -   `mult_array_merge($array1, $array2)`: 多维数组合并。
    -   `implode_quot($glue, array $pieces, $diffnum = false)`: 数组转换为带引号字符串。
    -   `is_multi_array($array)`: 判断是否为多维数组。
    -   `get_tree($data, $tid, $idField, $pidField, $sonName = 'son')`: 生成无限级树。
    -   `get_mapping($array, $vValue, $vKey = null)`: 获取数据数组的映射数组。
-   **语言设置**:
    -   `get_backend_lang()`: 获取后台语言。
    -   `get_frontend_lang()`: 获取前台语言。
    -   `get_default_lang()`: 获取默认语言（从数据库 `cms_area` 表中获取 `is_default` 为 1 的 `acode`）。
    -   `set_backend_lang(string $lg)`: 设置后台语言。
    -   `set_forntend_lang(string $lg)`: 设置前台语言。
-   **URL 生成**:
    -   `bdurl($type, $urlname, $pagetype, $scode, $sortfilename, $id = '', $contentfilename = '')`: 生成 CMS 模块的 URL。
-   **文件与目录操作**:
    -   `check_dir($path, $create = false)`: 检测目录是否存在，可选择创建。
    -   `create_dir($path)`: 创建目录。
    -   `check_file($path, $create = false, $content = null)`: 检查文件是否存在，可选择创建。
    -   `create_file($path, $content = null, $over = false)`: 创建文件。
-   **其他**:
    -   `mark($string)`: 在字符串中标记关键词。
    -   `network_match($ip, $network)`: 判断 IP 是否在子网中。
    -   `format_bytes($data)`: 字节转换为单位（B, KB, MB 等）。
    -   `resize_img(string|null $src_image, int $max_width = 0, int $max_height = 0, int $img_quality = 90)`: 缩放图片。
    -   `replaceEditorDomain(string $content, string $newDomain = '')`: 替换富文本中的媒体链接域名。
    -   `addEditorDomain(string $content, string $domain)`: 为富文本中的媒体链接添加域名。

### 4. `library/Bootstrap.php`

该类继承自 `think\Paginator`，对 ThinkPHP 的分页器进行了扩展和定制，以适应 CMS 模块的页面样式和功能需求。

-   **主要方法**:
    -   `getPreviousButton()`: 获取上一页按钮。
    -   `totalshow()`: 显示总页数信息。
    -   `showlastpage()`: 显示尾页按钮。
    -   `showfirstpage()`: 显示首页按钮。
    -   `afivepage()`: 显示后五页按钮（目前被注释掉）。
    -   `bfivepage()`: 显示前五页按钮（目前被注释掉）。
    -   `getNextButton()`: 获取下一页按钮。
    -   `gopage()`: 生成跳转页码的表单。
    -   `getLinks()`: 生成页码链接。
    -   `render()`: 渲染完整的分页 HTML。
    -   `pageData()`: 生成分页数据数组，包含首页、上一页、下一页、尾页、分页条 HTML、当前页、总页数、总行数等信息。
    -   `getAvailablePageWrapper($url, $page)`: 生成可点击的页码按钮 HTML。
    -   `getDisabledTextWrapper($text)`: 生成禁用的页码按钮 HTML。
    -   `getActivePageWrapper($text)`: 生成激活的页码按钮 HTML。
    -   `getDots($text = '...')`: 生成省略号按钮 HTML。
    -   `getUrlLinks(array $urls)`: 批量生成页码按钮。
    -   `getPageNumberLinks(array $urls)`: 批量生成数字页码按钮。
    -   `getPageLinkWrapper($url, $page)`: 生成普通页码按钮。

### 5. `taglib/Bd.php`

该文件定义了 CMS 模块在 ThinkPHP 模板中使用的自定义标签库。这些标签简化了在模板中调用后端数据和逻辑的操作。

-   **注册标签**:

    -   `sort`: 获取当前分类的子分类列表。
    -   `nav`: 导航列表。
    -   `list`: 内容列表。
    -   `slide`: 轮播图列表。
    -   `link`: 友情链接列表。
    -   `content`: 指定内容详情。
    -   `position`: 面包屑导航。
    -   `select`: 多条件筛选。
    -   `selectall`: 多条件筛选的“全部”链接。
    -   `search`: 搜索列表。
    -   `comment`: 文章评论列表。
    -   `commentsub`: 文章子评论列表。
    -   `message`: 留言列表。
    -   `form`: 自定义表单链接。
    -   `formlist`: 表单数据列表。
    -   `tags`: 内容标签列表。
    -   `pics`: 内容图片列表。
    -   `qrcode`: 生成二维码图片。
    -   `loop`: 循环标签。

-   **标签方法示例**:
    -   **`tagSort($tag, $content)`**: 用于获取子分类列表。
        -   属性: `scode` (必需), `alias`, `empty`, `key`, `mod`
        -   示例: `{bd:sort scode="$sort.scode" id="subsort"}{/bd:sort}`
    -   **`tagNav($tag, $content)`**: 用于生成导航。
        -   属性: `parent`, `num`, `scode`, `alias`, `empty`, `key`, `mod`
        -   示例: `{bd:nav parent="0" id="nav"}{/bd:nav}`
    -   **`tagList($tag, $content)`**: 用于获取内容列表。
        -   属性: `num`, `alias`, `empty`, `key`, `mod`, `scode`, `page`, `start`, `filter`, `tags`, `isico`, `ispics`, `istop`, `isrecommend`, `isheadline`, `fuzzy`, `order`
        -   示例: `{bd:list num="10" page="true"}{/bd:list}`
    -   **`tagContent($tag, $content)`**: 用于获取指定内容。
        -   属性: `id`, `scode`, `empty`, `alias`
        -   示例: `{bd:content id="$id"}{/bd:content}`
    -   **`tagPosition($tag, $content)`**: 用于生成面包屑导航。
        -   属性: `separator`, `separatoricon`, `indextext`, `indexicon`
        -   示例: `{bd:position /}`
    -   **`tagQrcode($tag, $content)`**: 用于生成二维码。
        -   属性: `string` (必需)
        -   示例: `{bd:qrcode string="$content.url" /}`

## 模块使用示例 (模板中)

```html
<!-- 获取子分类列表 -->
{bd:sort scode="$sort.scode" id="subsort"}
<a href="{$subsort.url}">{$subsort.name}</a>
{/bd:sort}

<!-- 获取导航 -->
{bd:nav parent="0" id="nav"}
<a href="{$nav.url}">{$nav.name}</a>
{/bd:nav}

<!-- 获取内容列表 -->
{bd:list num="10" page="true" id="article"}
<h3>{$article.title}</h3>
<p>{$article.description}</p>
{/bd:list} {$page|raw}
<!-- 显示分页HTML -->

<!-- 获取指定内容 -->
{bd:content id="$id" id="detail"}
<h1>{$detail.title}</h1>
<div>{$detail.content|raw}</div>
{/bd:content}

<!-- 面包屑导航 -->
{bd:position separator=" > " indextext="首页" /}

<!-- 生成二维码 -->
{bd:qrcode string="http://www.example.com" /}
```

## 数据库交互

模块通过 `think\facade\Db` 和自定义的模型类（如 `app\index\model\cms\ContentSort`, `app\index\model\cms\Content`, `app\index\model\cms\Slide`, `app\index\model\cms\Link`, `app\index\model\cms\Extfield`, `app\index\model\cms\MemberComment`, `app\index\model\cms\Message`, `app\index\model\cms\Form` 等）与数据库进行交互。

## 扩展与定制

-   **辅助函数**: `common.php` 中提供了大量辅助函数，可以直接在模块内或全局范围内调用。
-   **分页器**: 可以通过修改 `modules\cms\library\Bootstrap.php` 来定制分页器的显示样式和逻辑。
-   **自定义标签**: `modules\cms\taglib\Bd.php` 定义了丰富的自定义标签，可以根据需求添加新的标签或修改现有标签的逻辑。
-   **模型层**: 模块的数据操作主要通过 `app\index\model\cms` 目录下的模型类完成，可以通过修改这些模型来调整数据查询和处理逻辑。

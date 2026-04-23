## CMS插件目录结构

```
modules/
└── cms/
    ├── Cms.php                                 //插件主要入口文件
    ├── common.php                              //插件函数库
    ├── info.ini                                //插件信息
    ├── app/
    │   ├── admin/                              //插件后台部分
    │   │   ├── controller/cms                  //插件后台控制器
    │   │   ├── model/cms                       //插件后台模型
    │   │   ├── lang/zh-cn/cms                  //语言包文件
    │   │   └── view/cms                        //插件后台模版
    │   ├── index/
    │   │   ├── controller/cms                  //插件前台控制器
    │   │   ├── model/cms                       //插件前台模型
    │   │   └── route/                          //插件路由文件
    │   │       └── cms.php
    ├── library/
    │   └── Bootstrap.php                       //分页类
    ├── public/
    │   ├── modules/cms/js/
    │   │   └── change_lang.js                  //切换语言
    │   └── template/cms/default                //模版的静态资源（css、js等文件）
    ├── taglib/
    │   └── Bd.php                              //模版标签库
    └── template/                               //模版库 （模版安装后都会在这里）
        └── cms/
            └── default/                        //默认的cms模版
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

### 1. `Cms.php`

模块的入口类，定义了模块的生命周期方法。

- **`AppInit()`**: 应用初始化时调用，主要用于引入 `common.php` 和绑定 ThinkPHP 的分页器到自定义的 `Bootstrap` 类。
  - 引入 `common.php`：`include_once __DIR__ . '/common.php';`
  - 绑定分页器：`bind('think\Paginator', 'modules\cms\library\Bootstrap');`
- **`install()`**: 模块安装时调用，目前只返回 `true`。
- **`uninstall()`**: 模块卸载时调用，目前只返回 `true`。

### 2. `common.php`

该文件包含了一系列辅助函数，用于提供各种常用功能，例如：

- **用户及环境信息获取**:
  - `get_user_bs($bs = null)`: 获取用户浏览器类型。
  - `get_user_os($osstr = null)`: 获取用户操作系统类型。
  - `get_user_ip()`: 获取用户 IP 地址。
- **时间日期处理**:
  - `get_datetime($timestamp = null)`: 格式化时间戳为日期时间。
  - `get_date($timestamp = null)`: 格式化时间戳为日期。
  - `get_date_diff($startstamp, $endstamp, $return = 'm')`: 计算时间戳差值（年、月、日）。
  - `get_month_days($date, $start = 0, $interval = 1, $retamp = false)`: 获取间隔月份的起始及结束日期。
  - `style($string, $style)`: 格式化时间戳或日期字符串。
- **字符串处理**:
  - `get_strpos($string, $find, $n)`: 获取字符串第 N 次出现位置。
  - `escape_string($string)`: 转义字符串、数组、对象中的 HTML 实体和斜杠。
  - `decode_string($string)`: 反转义 HTML 实体和斜杠。
  - `decode_slashes($string)`: 反转义斜杠。
  - `encrypt_string($string)`: 字符串双层 MD5 加密。
  - `clear_html_blank($string)`: 清洗 HTML 代码中的空白符号。
  - `trim_slash($string)`: 去除字符串两端斜线。
  - `len($string, $length)`: 截取字符串。
  - `lencn($string, $length)`: 截取中英混合字符串，中文算两个字符。
  - `dropblank($string)`: 清除 HTML 空白符号。
  - `bd_substr($string, $strat, $length)`: 截取字符串并清除 HTML 标签。
  - `drophtml($str)`: 清除 HTML 标签与换行。
  - `substr_both($string, $strat, $length)`: 中英混合字符串截取。
  - `strlen_both($string)`: 中英混合字符串长度。
  - `preg_replace_r($search, $replace, $subject)`: 递归替换字符串。
  - `get_auto_code($string, $start = '1')`: 获取字符串型自动编码。
- **数据结构转换与操作**:
  - `object_to_array($object)`: 转换对象为数组。
  - `array_to_object($array)`: 转换数组为对象。
  - `in_object($needle, $object)`: 判断值是否在对象中。
  - `result_value_search($needle, $result, $skey)`: 在结果集中查找指定字段父节点是否存在。
  - `mult_array_merge($array1, $array2)`: 多维数组合并。
  - `implode_quot($glue, array $pieces, $diffnum = false)`: 数组转换为带引号字符串。
  - `is_multi_array($array)`: 判断是否为多维数组。
  - `get_tree($data, $tid, $idField, $pidField, $sonName = 'son')`: 生成无限级树。
  - `get_mapping($array, $vValue, $vKey = null)`: 获取数据数组的映射数组。
- **语言设置**:
  - `get_backend_lang()`: 获取后台语言。
  - `get_frontend_lang()`: 获取前台语言。
  - `get_default_lang()`: 获取默认语言（从数据库 `cms_area` 表中获取 `is_default` 为 1 的 `acode`）。
  - `set_backend_lang(string $lg)`: 设置后台语言。
  - `set_forntend_lang(string $lg)`: 设置前台语言。
- **URL 生成**:
  - `bdurl($type, $urlname, $pagetype, $scode, $sortfilename, $id = '', $contentfilename = '')`: 生成 CMS 模块的 URL。
- **文件与目录操作**:
  - `check_dir($path, $create = false)`: 检测目录是否存在，可选择创建。
  - `create_dir($path)`: 创建目录。
  - `check_file($path, $create = false, $content = null)`: 检查文件是否存在，可选择创建。
  - `create_file($path, $content = null, $over = false)`: 创建文件。
- **其他**:
  - `mark($string)`: 在字符串中标记关键词。
  - `network_match($ip, $network)`: 判断 IP 是否在子网中。
  - `format_bytes($data)`: 字节转换为单位（B, KB, MB 等）。
  - `resize_img(string|null $src_image, int $max_width = 0, int $max_height = 0, int $img_quality = 90)`: 缩放图片。
  - `replaceEditorDomain(string $content, string $newDomain = '')`: 替换富文本中的媒体链接域名。
  - `addEditorDomain(string $content, string $domain)`: 为富文本中的媒体链接添加域名。

### 3. `taglib/Bd.php`

该文件定义了 CMS 模块在 ThinkPHP 模板中使用的自定义标签库。这些标签简化了在模板中调用后端数据和逻辑的操作。

- **注册标签**:
  - `sort`: 获取当前分类的子分类列表。
  - `nav`: 导航列表。
  - `list`: 内容列表。
  - `slide`: 轮播图列表。
  - `link`: 友情链接列表。
  - `content`: 指定内容详情。
  - `position`: 面包屑导航。
  - `select`: 多条件筛选。
  - `selectall`: 多条件筛选的“全部”链接。
  - `search`: 搜索列表。
  - `comment`: 文章评论列表。
  - `commentsub`: 文章子评论列表。
  - `message`: 留言列表。
  - `form`: 自定义表单链接。
  - `formlist`: 表单数据列表。
  - `tags`: 内容标签列表。
  - `pics`: 内容图片列表。
  - `qrcode`: 生成二维码图片。
  - `loop`: 循环标签。

- **标签方法示例**:
  - **`tagSort($tag, $content)`**: 用于获取子分类列表。
    - 属性: `scode` (必需), `alias`, `empty`, `key`, `mod`
    - 示例: `{bd:sort scode="$sort.scode" id="subsort"}{/bd:sort}`
  - **`tagNav($tag, $content)`**: 用于生成导航。
    - 属性: `parent`, `num`, `scode`, `alias`, `empty`, `key`, `mod`
    - 示例: `{bd:nav parent="0" id="nav"}{/bd:nav}`
  - **`tagList($tag, $content)`**: 用于获取内容列表。
    - 属性: `num`, `alias`, `empty`, `key`, `mod`, `scode`, `page`, `start`, `filter`, `tags`, `isico`, `ispics`, `istop`, `isrecommend`, `isheadline`, `fuzzy`, `order`
    - 示例: `{bd:list num="10" page="true"}{/bd:list}`
  - **`tagContent($tag, $content)`**: 用于获取指定内容。
    - 属性: `id`, `scode`, `empty`, `alias`
    - 示例: `{bd:content id="$id"}{/bd:content}`
  - **`tagPosition($tag, $content)`**: 用于生成面包屑导航。
    - 属性: `separator`, `separatoricon`, `indextext`, `indexicon`
    - 示例: `{bd:position /}`
  - **`tagQrcode($tag, $content)`**: 用于生成二维码。
    - 属性: `string` (必需)
    - 示例: `{bd:qrcode string="$content.url" /}`

## 模板使用示例

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

### 4. `library/Bootstrap.php`

该类继承自 `think\Paginator`，对 ThinkPHP 的分页器进行了扩展和定制，以适应 CMS 模块的页面样式和功能需求。

- **主要方法**:
  - `getPreviousButton()`: 获取上一页按钮。
  - `totalshow()`: 显示总页数信息。
  - `showlastpage()`: 显示尾页按钮。
  - `showfirstpage()`: 显示首页按钮。
  - `afivepage()`: 显示后五页按钮（目前被注释掉）。
  - `bfivepage()`: 显示前五页按钮（目前被注释掉）。
  - `getNextButton()`: 获取下一页按钮。
  - `gopage()`: 生成跳转页码的表单。
  - `getLinks()`: 生成页码链接。
  - `render()`: 渲染完整的分页 HTML。
  - `pageData()`: 生成分页数据数组，包含首页、上一页、下一页、尾页、分页条 HTML、当前页、总页数、总行数等信息。
  - `getAvailablePageWrapper($url, $page)`: 生成可点击的页码按钮 HTML。
  - `getDisabledTextWrapper($text)`: 生成禁用的页码按钮 HTML。
  - `getActivePageWrapper($text)`: 生成激活的页码按钮 HTML。
  - `getDots($text = '...')`: 生成省略号按钮 HTML。
  - `getUrlLinks(array $urls)`: 批量生成页码按钮。
  - `getPageNumberLinks(array $urls)`: 批量生成数字页码按钮。
  - `getPageLinkWrapper($url, $page)`: 生成普通页码按钮。

## 控制器扩展与钩子机制

CMS 核心通过事件驱动（Event-Driven）和视图钩子（View Hooks）系统，允许开发者在不修改核心代码的情况下，对业务逻辑和 UI 界面进行深度扩展。

### 1. 后端事件驱动

基于 ThinkPHP 的 `Event` 机制，控制器在执行关键操作时会触发相应的观察者。
**逻辑链路**：`Action (控制器行为)` -> `Trigger (触发事件)` -> `Observer (观察者响应)` -> `Intercept (结果反馈/拦截)`。

#### 核心方法：`triggerObserver`

在 `Base` 控制器中调用，主要用于业务流程的拦截或数据的动态修正。

#### EventContext 数据上下文

在 `Before` 类钩子中，系统会传递 `EventContext` 对象，开发者可以通过它操作数据流：

- `$context->getData()`: 获取当前表单提交的原始数据。
- `$context->setData($data)`: 修改数据后存回，将直接影响最终的入库结果。
- **拦截机制**：调用 `$context->intercept('错误消息')` 可中断当前操作并向前端返回 JSON 错误提示。

### 2. 视图钩子系统

视图钩子允许模块向页面的预留坑位（Placeholder）动态注入 HTML 片段或功能按钮。

#### 后端声明：`assignHook`

控制器通过 `assignHook($hooks, $row)` 显式声明页面可注入的坑位。

- **$hooks**: 字符串数组，定义当前页面模板中预留的坑位名称。
- **$row**: 可选。当前操作的对象数据，供观察者根据数据内容决定注入逻辑。

#### 标准坑位分布

- `main_top`: 表单最顶部（通常用于展示提示信息）。
- `main_mid`: 核心字段之后，扩展字段（#extend）之前。
- `main_bottom`: 表单主体最下方。
- `side_top` / `side_bottom`: 右侧折叠面板的最上方或最下方。
- `footer`: 底部按钮区（通常注入在“保存”按钮左侧）。
- `scripts`: **核心 JS 脚本注入位**。

#### 模板引用建议

为了系统的健壮性，模板中引用的钩子变量必须先进行 `isset` 检查：

```html
{if isset($view_hooks.main_mid)} {$view_hooks.main_mid|raw} {/if}
```

### 3. JS 脚本注入

这是系统实现前后端无缝联动最核心的部分，支持闭包共享与分片渲染。

#### 运行态：闭包共享

注入到 `scripts` 坑位的 JS 内容将被放置在页面主逻辑 `layui.use(["badou"], function(){ ... })` 的**闭包内部**。

- **优势**：注入的脚本可直接访问作用域内的局部变量（如 `bdForm`, `badou`）和内部函数（如 `updateSubscode`, `getFieldHtml`），无需全局污染。

#### 自动标签剥离机制

为了方便开发，系统在处理 `scripts` 钩子时会自动执行正则剥离：

```php
$scripts = preg_replace('/<script[^>]*>(.*?)<\/script>/is', '$1', $scripts);
```

**开发者利好**：您可以在观察者的模板文件中保留 `<script>` 标签以获得 IDE 代码高亮，系统会自动将其转换为纯逻辑代码并完美融入主脚本块。

### 4. 前端组件钩子

前端 `badou.js` 库内置了 `hooks` 管理器，支持将 AJAX 请求路径自动映射为 JS 监听事件。

- **映射规则**：请求路径 `cms.content/getFieldHtml` 会自动对应前端钩子名 `cms.content.getFieldHtml`。
- **监听示例**：

```javascript
layui.badou.hooks.add("cms.content.getFieldHtml", function (data) {
  // data 为接口返回的 JSON 对象
  // 可在此处执行伴随接口刷新而触发的 DOM 额外操作
});
```

### 5. 全局初始化钩子

`cms_admin.init` 这是系统最先触发的全局钩子，位于 `Base.php` 的 `initialize` 方法末尾。

- **实战案例**：`Inquiry` 模块利用此钩子向所有 CMS 页面注入了币种列表（`currency_list`），实现了在不改动 CMS 核心控制器的情况下，跨模块共享全局业务配置。

## 实战案例

为了让开发者更好地理解这套机制，我们以开发一个名为 `Demo` 的插件为例，展示如何通过观察者模式干预 **CMS 内容编辑** 流程。

### 第一步：控制器侧 (核心代码)

在 `app\admin\controller\cms\Content.php` 中，核心逻辑已经预留了钩子：

```php
public function edit() {
    // ... 获取数据 $row ...
    if ($this->request->isPost()) {
        $data = $this->getPostData('row/a');

        // 1. 触发“修改前”观察者
        $context = new EventContext($data, ['row' => $row]);
        $this->triggerObserver('BeforeEdit', $context, $this);
        if ($context->isIntercepted()) {
            $this->error($context->getMessage()); // 观察者可以拦截并报错
        }
        $data = $context->getData(); // 获取观察者可能修改后的数据

        $row->save($data);

        // 2. 触发“修改后”观察者
        $this->triggerObserver('AfterEdit', $row, $data, $this);
        $this->success('修改成功');
    }

    // 3. 声明视图钩子位置
    $this->assignHook('edit', ['main_mid', 'scripts'], $row->toArray());
    return $this->view->fetch();
}
```

### 第二步：观察者侧 (插件逻辑)

在插件目录创建观察者类 `modules\demo\observer\admin\controller\Content.php`：

```php
namespace modules\demo\observer\admin\controller;

use badou\EventContext;
use think\facade\View;

class Content {
    // 监听“修改前”：根据业务逻辑拦截
    public function onBeforeEdit(EventContext $context, $controller) {
        $data = $context->getData();
        if (str_contains($data['title'], '违禁词')) {
            $context->intercept('标题包含违禁内容，请修改！');
        }
        return true;
    }

    // 监听“视图钩子”：注入 UI 和 脚本
    public function onViewHook($params) {
        $method = $params['method'] ?? '';
        // 仅在编辑页面注入
        if ($method !== 'edit') return [];

        // 路径指向插件内部的模板
        $tpl = MODULE_PATH . 'demo/view/hook/content.html';

        return [
            // 注入到主体中间区域
            'main_mid' => View::fetch($tpl, array_merge($params, ['hook_type' => 'ui'])),
            // 注入脚本（自动标签剥离）
            'scripts'  => View::fetch($tpl, array_merge($params, ['hook_type' => 'js']))
        ];
    }
}
```

### 第三步：视图侧 (插件模板)

在 `modules\demo\view\hook\content.html` 中，通过 `hook_type` 区分输出内容：

```html
{if $hook_type == 'ui'}
<div class="layui-form-item">
  <label class="layui-form-label">插件扩展</label>
  <div class="layui-input-block">
    <input
      type="text"
      name="row[demo_ext]"
      value="{$row.demo_ext|default=''}"
      class="layui-input"
    />
    <p class="help-block">这是通过 Demo 观察者动态注入的字段</p>
  </div>
</div>
{elseif $hook_type == 'js'}
<script>
  // 此脚本会自动融入主页面的 layui.use 闭包
  console.log("当前正在编辑的内容 ID 是：", "{$row.id}");

  // 可以直接调用主页面定义的函数
  if (typeof getFieldHtml === "function") {
    console.log("主页面函数 getFieldHtml 可用");
  }
</script>
{/if}
```

## FAQ

- **多语翻译不一致**：请检查 `cms_content_sort` 表。同步翻译依赖 `aucode`（关联码）。如果同一栏目在不同语言下的 `aucode` 不一致，系统将无法正确关联翻译。
- **脚本未生效**：
  1. 检查观察者返回的数组键名是否为 `scripts`。
  2. 确认控制器在调用 `assignHook` 时，`$hooks` 数组参数中是否包含了 `scripts`。
  3. 确认模板文件中是否正确引用了 `{$view_hooks.scripts|raw}`。

---

## 数据库交互

模块通过 `think\facade\Db` 和自定义的模型类（如 `app\index\model\cms\ContentSort`, `app\index\model\cms\Content`, `app\index\model\cms\Slide`, `app\index\model\cms\Link`, `app\index\model\cms\Extfield`, `app\index\model\cms\MemberComment`, `app\index\model\cms\Message`, `app\index\model\cms\Form` 等）与数据库进行交互。

## 扩展与定制

- **辅助函数**: `common.php` 中提供了大量辅助函数，可以直接在模块内或全局范围内调用。
- **分页器**: 可以通过修改 `modules\cms\library\Bootstrap.php` 来定制分页器的显示样式和逻辑。
- **自定义标签**: `modules\cms\taglib\Bd.php` 定义了丰富的自定义标签，可以根据需求添加新的标签或修改现有标签的逻辑。
- **模型层**: 模块的数据操作主要通过 `app\index\model\cms` 目录下的模型类完成，可以通过修改这些模型来调整数据查询和处理逻辑。

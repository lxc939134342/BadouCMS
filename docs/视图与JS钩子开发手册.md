# BadouAdmin 模块化扩展与钩子开发指南 (全量详尽版)

本文档旨在指导开发者通过“观察者模式”对 BadouAdmin 进行无损扩展，实现不改代码而增强功能的目标。

---

## 一、 系统核心架构：事件驱动 (Event-Driven)

BadouAdmin 的扩展基于 ThinkPHP 的 `Event` 机制。其逻辑链路为：
**控制器行为 (Action)** -> **触发事件 (Trigger)** -> **观察者响应 (Observer)** -> **结果反馈/拦截 (Return/Intercept)**。

---

## 二、 生命周期钩子：流程控制与数据拦截

在控制器（如 `Content`）的 CRUD 过程中，系统提供了两级事件触发：
1.  **类级别事件**：如 `app\admin\controller\cms\Content.BeforeAdd`。
2.  **模块级别事件**：如 `cms_observer.BeforeAdd`。

### 1. 核心方法：`triggerObserver`
在 `Base` 控制器中执行，用于流程拦截或数据修改。

### 2. EventContext 数据上下文
在 `Before` 类钩子中，系统会传递 `EventContext` 对象：
- `$context->getData()`: 获取当前表单提交的原始数据。
- `$context->setData($data)`: 修改数据后再存回，影响最终入库结果。
- **拦截机制**：调用 `$context->intercept('错误消息')` 可以直接中断操作并向前端返回错误提示。

---

## 三、 视图钩子系统：动态 UI 注入

视图钩子允许模块向页面的特定位置注入 HTML 片段或功能按钮。

### 1. 后端开启：`assignHook`
控制器通过 `assignHook($hooks, $row)` 显式声明坑位。
- **$hooks**: 必传。这是一个字符串数组，定义了当前页面模板中预留的坑位名。
- **$row**: 可选。当前正在编辑的对象数据，方便观察者根据内容决定注入什么。

### 2. 标准坑位分布
- `main_top`: 表单最顶部（通常放提示信息）。
- `main_mid`: 核心字段之后（如缩略图后），扩充字段（#extend）之前。
- `main_bottom`: 表单主体最后。
- `side_top` / `side_bottom`: 右侧折叠面板的最上方或最下方。
- `footer`: 底部按钮区（“保存”按钮左侧）。
- `scripts`: **核心脚本位**（见第四节）。

### 3. 模板防御机制
为了极致的稳定性，模板必须使用 `isset` 检查：
```html
{if isset($view_hooks.main_mid)}
    {$view_hooks.main_mid|raw}
{/if}
```

---

## 四、 JS 脚本注入：闭包共享与分片渲染

这是本系统最精妙的部分。

### 1. 运行态：闭包共享
注入到 `scripts` 坑位的内容会被放置在页面主 `layui.use(["badou"], function(){ ... })` 的**闭包内部**。这意味着：
- 注入的脚本可以直接访问页面定义的局部变量（如 `bdForm`, `badou`）。
- 注入的脚本可以直接调用页面内部函数（如 `updateSubscode`, `getFieldHtml`）。

### 2. 开发态：单文件分片渲染
我们推荐在观察者中使用 `View::fetch` 渲染一个统一的模板文件，通过 `hook_type` 区分输出。
**特别注意：自动标签剥离机制**
系统在 `assignHook` -> `triggerObserverView` 的过程中，会自动执行正则剥离：
```php
$scripts = preg_replace('/<script[^>]*>(.*?)<\/script>/is', '$1', $scripts);
```
**好处**：您可以在观察者模板中带着 `<script>` 标签写代码以享受 IDE 高亮，但在最终生成的页面中它会变成纯 JS 逻辑，完美融入主脚本块，不会产生多个冗余标签。

---

## 五、 前端 JS 自动化钩子

前端 `badou.js` 库提供了 `hooks` 管理器，它将 AJAX 请求 URL 自动映射为 JS 事件。

- **映射规则**：请求 `cms.content/getFieldHtml` 时，会自动触发名为 `cms.content.getFieldHtml` 的前端钩子。
- **监听示例**：
```javascript
layui.badou.hooks.add('cms.content.getFieldHtml', function(data) {
    // data 是接口返回的 JSON 对象
    // 您可以在这里跟随接口刷新而执行额外的 DOM 操作
});
```

---

## 六、 全局初始化钩子：`cms_admin.init`

位于 `Base.php` 的 `initialize` 方法末尾。这是最先触发的钩子。
**实战案例**：
`Inquiry` 模块通过此钩子向所有 CMS 控制器注入了货币列表（`currency_list`）数据，确保了内容编辑页面的多币种展示功能，而无需在每个控制器的代码里手动去查数据库。

---

## 七、 技术排查提示 (Troubleshooting)

- **多语翻译不工作**：请检查 `cms_content_sort` 表。系统同步翻译依赖 `aucode`（关联码）。如果同一组栏目在不同语言下的 `aucode` 不一致，`getScodeOfAcode` 方法将无法定位到目标栏目。
- **脚本不执行**：检查观察者返回的数组键名是否叫 `scripts`，且控制器调用时是否包含了 `scripts`。

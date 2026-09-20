---
name: badoucms
description: "BadouCMS 项目开发规范：ThinkPHP 8 MVC 分层、Model 数据访问、try/catch 响应控制，以及 BadouAdmin 表单、表格、上传和后台交互组件优先使用。适用于新增功能、接口、后台页面、Bug 修复和重构。"
---

# BadouCMS 开发规范

本 Skill 用于当前 BadouCMS 项目的日常开发。默认项目技术栈为 PHP 8.1+、ThinkPHP 8.1、Think ORM、多应用结构，以及基于 Layui/Pear Admin 的 BadouAdmin 前端封装。

## 不可违反的规则

### 1. `try {}` 内禁止直接结束页面执行

在 `try {}` 代码块中，禁止调用会直接返回响应或抛出响应异常的控制器方法，包括但不限于：

- `$this->success(...)`
- `$this->error(...)`
- 用户常写错的 `$this->succsess(...)`
- 其他项目中具有相同“立即输出响应并结束执行”效果的 helper

`try {}` 内只负责执行和提交。`catch {}` 中应先完成回滚、日志等异常清理；清理完成后允许直接调用 `$this->error()` 返回错误。成功响应通常放在完整的 `try/catch` 之后。

#### 推荐写法

```php
$this->model->startTrans();

try {
    $this->modelValidateFunction($data);

    $context = new EventContext($data);
    $this->triggerObserver('BeforeAdd', $context, $this);
    if ($context->isIntercepted()) {
        throw new \RuntimeException($context->getMessage());
    }

    $result = $this->model->save($context->getData());
    if ($result === false) {
        throw new \RuntimeException(__('No rows were added'));
    }

    $this->model->commit();
} catch (\Throwable $e) {
    $this->model->rollback();
    $this->error($e->getMessage());
}

$this->success(__('Operation completed'));
```

要点：

- 需要中止事务时，抛出 `\RuntimeException` 或项目已有的业务异常，不要在 `try` 中调用 `$this->error()`。
- `catch` 中先完成回滚、日志和必要清理，之后可以直接调用 `$this->error()`。
- `$this->success()` 应放在 `try/catch` 完整结束后，避免成功响应被异常处理逻辑捕获。
- 使用事务时，提交成功后才清理缓存、发送通知或触发后置动作；失败必须回滚。
- 不要为了绕过规则把响应 helper 改名、包装后继续放进 `try`。

### 2. 遵循 ThinkPHP 8 MVC 分层

项目是多应用结构，不要把所有逻辑堆到 Controller。新增代码按下面职责放置：

| 层 | 目录 | 职责 |
| --- | --- | --- |
| Controller | `app/admin/controller`、`app/api/controller`、`app/index/controller` | 接收请求、权限/参数入口、调用 Validate/Model/Service、组织响应、渲染视图 |
| Model | 对应应用的 `app/*/model`，或共享的 `app/common/model` | 数据库查询、关联查询、增删改、分页、数据持久化、与数据行紧密相关的转换 |
| Validate | `app/*/validate` | 字段和请求参数校验 |
| View | `app/admin/view`、`app/index/view` | HTML/模板、表单、表格、展示层交互 |
| Service | `modules/<module>/service` 或 `app/common/service` | 跨 Model 的业务编排、事务流程、第三方调用、复杂领域流程 |
| Route | `app/*/route` 或 `route` | 路由声明，不在路由文件实现业务 |

#### Controller 规则

Controller 应保持薄：

1. 读取请求参数并做必要的类型整理。
2. 调用 Validate、当前 Model 或 Service。
3. 处理权限、登录态、租户上下文和页面参数。
4. `assign` / `fetch` 或按项目约定返回 JSON/重定向。

Controller 中禁止新增以下数据访问写法：

```php
use think\facade\Db;

Db::name('xxx')->where(...)->select();
db('xxx')->where(...)->find();
$model->where(...)->save(...); // 复杂查询/持久化不得直接散落在 Controller
```

简单 CRUD 也应通过 Controller 持有的 Model 或 Model 方法完成；不要因为查询只有一行就绕过 Model。

#### Model 规则

- 表名、字段类型、时间戳、追加属性等写在 Model 中。
- 将可复用的查询封装为有语义的方法，例如 `findForTenant()`、`getList()`、`countNormalByTenant()`。
- Model 方法接收明确参数并返回稳定的数据结构，避免让 Controller 拼接 SQL 条件。
- 原始 SQL 仅在 ORM 无法合理表达且确有必要时使用，并封装在 Model/Service 中；必须参数化或安全处理表名，不能由用户输入直接拼接。
- 涉及多个 Model 的流程放到 Service 编排，具体表查询仍由各自 Model 负责。
- 新增数据表对应的 Model 放在实际应用或模块的 model 目录中，命名空间和目录大小写保持一致。

#### Service 规则

Service 适合承载：跨表业务、事务边界、幂等、第三方 API、队列/通知、复杂状态流转。Service 不应成为“另一个 Controller”，也不要把所有查询重新写成 `Db::name()`；优先调用 Model 的语义方法。

### 3. 代码注释与复杂度

- 新增代码中的注释一律使用中文；不要新增英文注释。已有英文注释无需为了统一风格而修改。
- 只为关键业务规则、状态流转、兼容逻辑、安全边界和不直观的实现补充注释；显而易见的赋值、判断和框架调用不写注释。
- 注释应说明“为什么这样做”或约束条件，避免逐行复述代码本身。
- 实现以简约、易读为优先：在一个 Controller、Model 或 Service 方法内能清晰完成的局部逻辑，不要为了形式上的分层再拆成多个私有方法、包装类或单用途 Service。
- 仅当逻辑会复用、承担独立业务语义、需要独立事务/错误处理，或拆分后能明显降低理解成本时，才抽离方法或 Service。方法粒度应与业务步骤匹配，不追求过细。
- 优先使用清晰的变量名、顺序流程和有限层级的条件判断表达意图；不要用过度抽象、回调链或无意义的中间层替代直接可读的代码。

### 4. 视图优先使用 BadouAdmin 封装组件

表单、表格列表和常用交互必须先查找并优先复用项目已有的 BadouAdmin 封装：

```text
public/assets/libs/badouadmin/
├── badou.js
├── bdForm.js
├── bdHttp.js
├── bdTable.js
├── bdTool.js
├── bdUpload.js
└── tableSearch.js
```

- 后台列表默认使用 `bdTable.api.init()` + `bdTable.render()`，不要直接重新实现分页、排序、批量删除、操作按钮和权限显隐。
- 后台表单默认使用 `layui-form` + `bdForm.api.bindevent()`，不要自行重写普通表单提交、通用 loading、成功/错误提示和关闭弹窗后的刷新流程。
- 上传优先使用 `bdUpload`，请求、弹窗和统一反馈优先使用 `badou` / `bdHttp`。
- 搜索优先使用 `bdTable` 集成的 `tableSearch` / 高级搜索；远程下拉和日期范围优先检查 `bdTool` 与项目已有实现。
- 使用 `data-operate-*` 和 `$auth->check()` 遵循现有权限显隐方式。
- 只有确认现有组件无法满足需求时，才新增组件或页面专用实现，并说明为什么不能复用。
- 页面脚本中不要把数据库查询、业务规则或权限判断写成前端“补丁”；这些应由后端 Model/Service/Controller 正确提供。

#### 标准表格写法

```html
{layout name="layout/default" /}

<div class="layui-card">
  <div class="layui-card-body">
    <table
      class="layui-hide"
      id="demo-table"
      data-operate-edit="{:$auth->check('module.controller/edit')}"
      data-operate-del="{:$auth->check('module.controller/del')}"
    ></table>
  </div>
</div>

<script type="text/html" id="toolbar-demo-table">
  <a href="javascript:;" lay-event="refresh" class="layui-btn btn-refresh layui-bg-black">
    <i class="fa fa-refresh"></i>
  </a>
  <button class="layui-btn layui-bg-green {:$auth->check('module.controller/add')?'':'hide'}" lay-event="add">
    <i class="fa fa-plus"></i>{:__('Add')}
  </button>
  <button class="layui-btn layui-bg-blue btn-disabled disabled {:$auth->check('module.controller/edit')?'':'hide'}" lay-event="edit">
    <i class="fa fa-pencil"></i>{:__('Edit')}
  </button>
  <button class="layui-btn layui-bg-red btn-disabled disabled {:$auth->check('module.controller/del')?'':'hide'}" lay-event="del">
    <i class="fa fa-trash"></i>{:__('Del')}
  </button>
</script>

<script>
layui.use(['badou'], function () {
  var bdTable = layui.bdTable;
  var table = layui.table;

  bdTable.api.init({
    table: table,
    extend: {
      index_url: 'module.controller/index',
      add_url: 'module.controller/add',
      edit_url: 'module.controller/edit',
      del_url: 'module.controller/del',
      multi_url: 'module.controller/multi',
      table: 'module_table',
    },
  });

  bdTable.render({
    elem: '#demo-table',
    url: "{:url('module.controller/index')}",
    pk: 'id',
    toolbar: '#toolbar-demo-table',
    cols: [[
      {type: 'checkbox'},
      {field: 'id', title: 'ID', width: 80, sort: true},
      {field: 'name', title: '名称', minWidth: 180, operate: 'LIKE'},
      {field: 'create_time', title: '创建时间', templet: bdTable.api.formatter.datetime},
      {title: "{:__('Operate')}", templet: bdTable.api.formatter.operate},
    ]],
  });
});
</script>
```

#### 标准表单写法

```html
<form class="layui-form" method="post">
  {:token_field()}
  {if isset($row)}<input type="hidden" name="row[id]" value="{$row.id}">{/if}

  <div class="layui-form-item">
    <label class="layui-form-label">名称</label>
    <div class="layui-input-block">
      <input
        type="text"
        name="row[name]"
        value="{$row.name|default=''|htmlentities}"
        class="layui-input"
        lay-verify="required"
      >
    </div>
  </div>

  <div class="layui-form-item layer-footer hide">
    <div class="layui-input-block">
      <button class="layui-btn btn-theme-color" lay-submit>保存</button>
      <button type="reset" class="layui-btn layui-btn-primary">重置</button>
    </div>
  </div>
</form>

<script>
layui.use(['badou'], function () {
  layui.bdForm.api.bindevent($('form.layui-form'));
});
</script>
```

### 5. BadouAdmin 开发细则

以下规则在保留上述视图约束的基础上，补充本项目实际 BadouAdmin 组件的使用边界。

#### 开始前先复用现有页面

新增或调整 `app/admin/view` 页面前，按以下顺序查找：

1. 当前模块内同类型页面；
2. `app/admin/view` 中相近的列表、表单、详情或上传页面；
3. `public/assets/libs/badouadmin` 对应组件 API；
4. 必要时才编写页面专属 JavaScript 或 CSS。

推荐检索：

```bash
rg -n "bdTable\.api\.init|bdTable\.render" app/admin/view
rg -n "bdForm\.api\.bindevent" app/admin/view
rg -n "bdUpload|bdTool|tableSearch" app/admin/view public/assets/libs/badouadmin
rg -n "data-operate-|\$auth->check" app/admin/view
```

普通后台列表、详情和面板页优先使用 `{layout name="layout/default" /}`；弹窗和独立表单页应跟随相邻同类页面使用 `layout/form` 或已有布局。不得自行重建后台导航、全局资源引用，或引入 Bootstrap、React、Vue 等无关 UI 框架来替代 BadouAdmin / Layui。

#### `bdTable` 详细约定

`bdTable.api.init()` 的 `extend` 是表格公共操作的统一入口：

- 列表接口使用 `index_url`；新增、编辑、删除、批量操作分别按需配置 `add_url`、`edit_url`、`del_url`、`multi_url`。
- `table` 使用实际数据表名，路由写法与当前模块已有代码保持一致。
- 没有某项能力时不虚设 URL；不将同一操作的 URL、弹窗和 Ajax 逻辑拆散到多个匿名事件里。
- 常规操作由 `bdTable` 处理，特殊详情、预览或业务动作才新增明确的按钮与事件。

表格字段优先复用 `bdTable.api.formatter`：

```js
{field: 'create_time', title: '创建时间', templet: bdTable.api.formatter.datetime}
{field: 'status', title: '状态', templet: bdTable.api.formatter.status}
{title: "{:__('Operate')}", templet: bdTable.api.formatter.operate}
```

状态列应同时声明搜索字典和展示颜色：

```js
{
  field: 'status',
  title: '状态',
  width: 120,
  templet: bdTable.api.formatter.status,
  searchList: {normal: '正常', hidden: '停用'},
  custom: {normal: 'green', hidden: 'red'},
}
```

搜索优先由列配置和 `tableSearch` 自动生成：

```js
{field: 'title', title: '标题', operate: 'LIKE'}
{field: 'status', title: '状态', searchList: {normal: '正常', hidden: '隐藏'}}
{field: 'update_time', title: '更新时间', searchType: 'time'}
```

- 多级字段可直接使用字段路径，交给 `bdTable` 的列字段处理能力；不要只为读取嵌套值复制 formatter。
- 只有复杂联动、固定筛选面板或 `tableSearch` 确实无法表达的筛选才自定义搜索 UI。
- 自定义行内按钮优先使用 `bdTable` 的 `buttons` / formatter 扩展，不复制完整操作列的编辑、删除和权限逻辑。
- 除非需求明确要求关闭，保留 `bdTable` 默认分页、排序、导出、打印和高级搜索能力。

#### `bdForm` 详细约定

- 表单使用 `class="layui-form"`，字段沿用 `row[field]`；需要令牌时使用 `{:token_field()}`，编辑时保留主键隐藏字段。
- 回显的文本类字段使用 `|htmlentities`，除非附近现有页面因字段类型采用了其他安全输出方式。
- 必填、数字、邮箱等体验校验用 `lay-verify`；它不能替代后端校验。
- 弹窗表单的底部按钮使用 `layer-footer hide`；独立页面的按钮布局、文本和颜色跟随同模块现有页面。
- 单选、复选、开关和下拉变更后需要重渲染时使用 Layui `form` API，不要手工改写 Layui 生成的渲染 DOM。
- 只有提交前需要补充临时数据、成功后刷新额外区域或进入特殊流程时，才通过 `bdForm.api.bindevent(form, success, error, submit)` 提供回调；仍以 `bdForm` 作为提交流程入口。
- 禁止以裸 `$.ajax`、`fetch` 或自行绑定 `form.on('submit')` 替代普通 `bdForm` 提交。

字段动态显示、开关同步与条件校验可使用 Layui 事件：

```js
form.on('switch', function (data) {
  // 仅处理当前页面的展示或字段同步
});
form.on('radio', updateCondition);
form.on('select', updateCondition);
```

条件隐藏字段时，必须同步移除会造成错误提交的 `lay-verify`；恢复显示时再恢复规则。所有选择器和事件必须限制在当前表单或弹窗内，避免影响其他后台页面。

#### 上传、请求、弹窗和辅助组件

- 图片、附件和多文件上传优先使用 `bdUpload` 的按钮、隐藏输入、预览与回填模式，并以同模块既有上传页面为准。
- 不要每页重新封装上传 token、上传结果解析、预览、删除、排序或字段回填；保持项目既有的 URL、附件 ID 或逗号分隔多值格式。
- 常规新增、编辑、删除由 `bdTable` 接管。特殊详情、预览和业务弹窗才直接使用 `layui.badou`、`badou.api` 或当前模块已有的 `layer` 模式。
- 请求优先用 `badou.api.ajax` / `badou.http`，保持统一 loading、错误提示、登录态和回调行为；不得新建另一套全局请求封装。
- 需要用户选择后才能继续的请求，后端返回 `code: 2` 和 `data.confirm`，由 `bdHttp` 统一弹窗并重放原请求；不要在页面中覆盖 `bdTable` 删除事件或重复编写 Ajax 错误回调。协议如下：

  ```php
  $this->result('检测到关联数据，是否继续？', [
      'confirm' => [
          'title' => '关联数据确认',
          'actions' => [
              ['text' => '处理全部关联数据', 'params' => ['all' => 1]],
              ['text' => '仅处理当前数据', 'params' => ['all' => 0]],
          ],
          'cancelText' => '取消',
      ],
  ], 0, 2);
  ```

  `actions` 最多两个，框架统一提供取消按钮。`params` 只能合并到原请求，不可由响应指定 URL、请求方法或前端代码；缺少 `data.confirm` 的 `code: 2` 保持普通错误处理。
- `badou.api.ajax(options, success)` 在成功回调执行后默认显示成功 Toast。若请求只用于页面初始化、静默刷新概览或回填数据，可在 `success` 回调末尾 `return false;` 阻止该默认提示；仍由组件关闭 loading。用户主动触发且没有自定义反馈的操作不要返回 `false`，以保留成功提示。

  ```js
  layui.badou.api.ajax({url: summaryUrl}, function (data) {
    updateSummary(data);
    return false; // 静默刷新，不显示默认“操作成功”提示
  });
  ```

- 弹窗尺寸、关闭和父页刷新先参考同模块已有页面；大尺寸页面可在当前页面局部配置 `badou.http.config.open.area`。
- 日期/时间选择优先使用 Layui `laydate`；远程下拉、选择器和通用工具先检查 `bdTool` 和既有实现。

#### 标签页与弹窗跳转

- `btn-addtab` 由后台框架转换为 hash 标签页路由，`href` 必须使用项目内部路由名，例如 `href="geo.content_pool"` 或 `href="geo.keyword_library"`。不要对 `btn-addtab` 使用 `{:url('geo.content_pool/index')}`：该 helper 会生成带后台入口文件的完整 URL，进入 hash 后会让入口文件名被误解析为控制器。
- `btn-dialog` 打开的是 iframe 弹窗，应使用 `{:url('geo.content_create/add')}` 这类完整 URL；普通页面跳转则跟随相邻页面的既有写法。
- 新增跳转前，先检索同一后台模块中 `btn-addtab` 和 `btn-dialog` 的现有链接格式，保持同一跳转机制使用同一种 URL 形式。

#### 权限显隐

所有后台敏感操作都要使用现有权限节点控制显示：

```html
<button class="layui-btn {:$auth->check('geo.content/add')?'':'hide'}" lay-event="add">
  {:__('Add')}
</button>

<table
  class="layui-hide"
  id="content"
  data-operate-edit="{:$auth->check('geo.content/edit')}"
  data-operate-del="{:$auth->check('geo.content/del')}"
></table>
```

- 新增、编辑、删除、批量操作、导入和导出均应按实际权限节点使用 `$auth->check()` 或 `data-operate-*`。
- 节点名称必须和实际后台路由、当前模块既有规则一致。
- 前端隐藏仅服务于 UI，不能替代后端鉴权；禁止在 JavaScript 中硬编码角色、管理员 ID、菜单名称或权限判断。

#### 页面专属 CSS 与 JavaScript

页面专属代码适用于字段联动、特殊预览、图表、报告、地图等 BadouAdmin 没有通用封装的场景，但必须遵守：

- 专属脚本只处理展示和交互，不承担权限、安全或业务规则校验。
- DOM 查询以当前页面、表单或弹窗容器为边界；避免重复绑定事件。
- CSS 必须使用页面根容器、唯一类或组件类作为前缀，不能覆盖后台全局标签样式、Layui 通用类或其他模块样式。
- 不污染 `window`；如果当前模块已有全局扩展模式，才依照它的既有约定扩展。
- 不因单个特殊页面直接改动 `public/assets/libs/badouadmin` 公共组件；只有跨页面通用、现有 API 无法支持且影响范围明确时才考虑扩展。

## 推荐开发流程

### 开始编码前

1. 阅读根目录 `AGENTS.md`，确认 GitNexus 和项目约束。
2. 在 `app`、`modules` 和 `public/assets/libs` 中查找相近功能，优先复用现有实现。
3. 明确本次修改涉及的 Controller、Model、Service、View 和 JS 文件。
4. 如果要修改已有 PHP 函数、类或方法，先按项目 AGENTS 要求运行 GitNexus impact analysis，并查看直接调用方、执行流程和风险级别；若为 HIGH/CRITICAL，先向用户明确报告影响后再编辑。
5. 如果 GitNexus 报索引过期，先在项目根目录运行 `npx gitnexus analyze`。

### 编码时

1. 先确定数据流：请求 → Controller → Validate/Service → Model → 响应/视图。
2. 把查询和数据写操作放入 Model，把跨 Model 流程放入 Service。
3. 事务用“`try` 中执行和提交，`catch` 中回滚并返回错误，成功响应放在外部”的模式；禁止在 `try {}` 中直接响应。
4. 视图先复用 BadouAdmin 组件，再考虑扩展组件。
5. 遵循附近文件的命名、命名空间、路由、权限、返回格式、token、缓存和多租户约定。
6. 修改现有功能时保持改动聚焦，不要顺手大范围重构无关遗留代码。
7. 新增关键注释使用中文，并在提交前删除无实际说明价值的注释；保持方法和类的粒度与业务复杂度相称。

### 完成编码后

至少执行以下检查：

```bash
# PHP 语法检查（替换为实际改动文件）
php -l app/admin/controller/xxx.php
php -l app/admin/model/xxx.php

# 检索响应终止方法；人工确认命中未处于 try 块内，同时覆盖常见拼写错误
rg -n '\$this->(success|succsess|error)\s*\(' app modules

# 检查 Controller 是否新增直接数据库访问
rg -n '\b(Db::|db\(|Db::name|Db::query|Db::execute)' app/*/controller modules/*/controller

# 后台页面复用与权限检查（替换为实际模块）
rg -n "bdTable\.api\.init|bdTable\.render|bdForm\.api\.bindevent" app/admin/view/<module>
rg -n "data-operate-|\$auth->check" app/admin/view/<module>
rg -n "\$\.ajax\(|fetch\(|form\.on\(['\"]submit" app/admin/view/<module>
```

上面的 `rg` 是人工复核入口，不是替代 AST/代码审查；需要判断命中是否位于 `try {}` 代码块中。`catch {}` 中完成回滚和清理后直接调用 `$this->error()` 是允许的。

如果准备提交代码，必须在提交前运行 `gitnexus_detect_changes()`，确认变更只影响预期的文件、符号和执行流程；发现意外影响时先修正，不要直接提交。

## Review 清单

- [ ] `try {}` 内没有 `$this->success()`、`$this->succsess()`、`$this->error()` 或同类立即响应 helper。
- [ ] 异常分支在响应前已正确 rollback 并完成必要清理；成功响应位于 `try/catch` 之后。
- [ ] Controller 没有新增 `Db::name()`、`db()`、原始 SQL 或散落的复杂查询/写操作。
- [ ] Model 承担数据访问，方法有清晰语义；跨 Model 流程由 Service 编排。
- [ ] 新增关键注释均为中文且说明必要的业务原因；未新增英文注释或逐行复述代码的无效注释。
- [ ] 实现保持直接易读，没有为形式上的抽象而拆出过多函数、方法或单用途 Service。
- [ ] 后台页面使用项目既有布局，未重建后台框架或引入无关 UI 框架。
- [ ] 视图表单使用项目约定的 `layui-form` 和 `bdForm`，字段、令牌与回显格式正确。
- [ ] 视图表格使用 `bdTable`，复用了已有 formatter、搜索、权限和批量操作能力。
- [ ] 上传、请求和弹窗优先复用 `bdUpload`、`badou`、`bdHttp` 与项目现有模式。
- [ ] 新组件确实是现有 `public/assets/libs` 中没有的能力，且实现可复用。
- [ ] 页面专属 CSS/JS 已限制作用域，不影响其他后台页面。
- [ ] PHP 语法、接口返回格式、权限、token、缓存、租户隔离和相关页面行为已检查。
- [ ] 提交前已执行 GitNexus 变更检测。

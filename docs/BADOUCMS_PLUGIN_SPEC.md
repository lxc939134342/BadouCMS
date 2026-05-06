# BadouCMS 插件开发规范 (AI 专用)

本文件定义了 BadouCMS 插件开发的固定上下文和代码规范。AI 在生成代码或提供建议时必须严格遵守。

## 1. 框架与环境

- **底层框架**: ThinkPHP 6 / 8 (当前项目为 TP8, 语法兼容 TP6)
- **插件体系**: 基于 BadouCMS 官方插件架构 (存放于 modules 目录)
- **数据库前缀**: `bd_` (代码中应动态适配前缀，不要硬编码)

## 2. 目录结构规范

所有插件必须位于 `/modules/` 目录下，子目录结构必须区分前后台：

```text
modules/
└── {插件名}/
    ├── app/
    │   ├── admin/           # 后台管理端
    │   │   ├── controller/  # 控制器
    │   │   ├── model/       # 模型
    │   │   └── view/        # 视图
    │   ├── index/           # 前台展示端
    │   │   ├── controller/
    │   │   ├── model/
    │   │   └── view/
    │   └── api/             # 接口端
    │       └── controller/
    ├── config.php           # 插件配置文件
    ├── install.php          # 安装脚本
    ├── info.ini             # 插件基本信息
    └── ...
```

## 3. 代码编写准则

### 控制器 (Controller)

- **命名空间**:
  - 后台: `namespace app\admin\controller\{插件名};`
  - 前台: `namespace app\index\controller\{插件名};`
- **继承基类**:
  - 后台通常扩展自: `app\common\controller\Backend`
  - 前台通常扩展自: `app\common\controller\Frontend`
- **输出规范**:
  - 页面跳转/渲染：`return $this->fetch();`
  - 接口返回：统一返回 JSON 格式数据

### 模型 (Model)

- **命名空间**: `app\{admin|index}\model\{插件名}`
- **数据库操作**: 统一使用 `think\facade\Db` 类进行链式操作。
- **命名约定**: 数据库字段名必须使用 **小写下划线** (snake_case)。

### 异常处理规范

- **严禁在 `try` 块内直接使用 `$this->success()` 或 `$this->error()`**。
- **原因**: 这些方法通过抛出异常来中断流程，在 `try` 内部会被捕获，导致逻辑混乱或死循环。
- **正确做法**: 在 `try` 块外部根据标志位或执行结果来统一调用结果输出方法。

### 业务逻辑

- 插件内的业务逻辑应封装在插件内部的 `model` 或 `service` 中。
- 严禁修改 `app/` 核心目录，功能必须闭环在 `modules/{插件名}/` 下。

## 4. 前端规范

- 页面 UI 基于 **Layui** 框架。
- 框架资源目录：`/public/assets/`
- 插件资源路径适配：`/public/modules/{插件名}/`。

---

**注意**: 在后续的所有对话中，除非明确指定，否则默认按照此规范开发插件。

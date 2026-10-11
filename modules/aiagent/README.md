# AI 后台助手

这是 BadouCMS 原生插件，插件名 `aiagent`。使用现有「模块开发」和「插件管理」的安装、启用、禁用及打包流程；不需要额外构建脚本。

部署与使用请看 [Agent 宝塔部署说明](../../docs/AI-Agent-宝塔部署.md)，包含 FPM 和 CLI／Supervisor；容器部署请看 [Docker 部署说明](../../docs/Docker部署.md)。

## 开发与打包

当前工作区保持启用状态，Agent 文件在项目 `app/` 和根目录 `config/agent.php` 中，由 `.modulerc` 登记归属，与其他已启用插件一致。

在「模块开发」中选择「AI 后台助手」→「禁用」→「打包」。禁用时，框架先将 `.modulerc` 中登记的文件收回 `modules/aiagent/app/` 和 `modules/aiagent/config/`，随后现有打包程序生成可安装的插件包。修改启用中的 Agent 时，直接编辑对应 `app/` 文件和根目录 `config/agent.php` 即可。打包后可重新启用。

也可在禁用后使用打包程序插件的 `php think package:plugin aiagent` 命令。

## 文件与依赖

- `Aiagent.php`：插件生命周期、浮窗钩子与队列命令注册。
- `.modulerc`：Agent 控制器、视图、模型、`app/common/agent`、`app/common/ai` 以及根配置的全局文件归属。
- `view/launcher.html`：后台浮窗入口，使用通用 `admin_body_end` 钩子。
- `config/agent.php`：流式响应、队列模式、代码改动策略；启用时位于项目根目录，禁用后收回插件目录。
- `install.sql`：五张 Agent 表；只创建缺失表，保留既有数据。
- `vendor/`：Neuron AI 4.0.0 与 PSR Event Dispatcher 1.0.0，包含独立 Composer 自动加载器。

ThinkPHP、Guzzle 和 PhpSpreadsheet 复用宿主框架。根 Composer 已移除 Neuron 专属依赖，SDK 由本插件启用时加载；PHP 当前支持 8.1—8.4。插件配置不会覆盖站点主动设置的 `agent` 运行时配置。

框架自动加载根目录 `config/agent.php`，后台与队列 worker 使用同一份配置。直接修改该文件即可；修改后需重启常驻 worker。安装阶段从安装包的 `config/agent.php` 加载，启用时迁移到根目录；禁用时将当前配置原样收回，并随插件打包。

框架禁止插件迁移 `config/app.php` 和 `config/badouadmin.php`；安装包或文件归属清单包含这两项时会拒绝处理，强制覆盖同样受此限制。

CMS 的工具配置与任务保留在 `modules/cms/agent/`，通过现有工具注册接口接入，不合并旧的「AI 智能创作」插件。

## 安装与启停

后台主模板需要通用 `admin_body_end` 钩子，插件迁移目录需要支持 `config`；当前框架和升级包已补上。插件不覆盖整个后台主模板。安装／升级补齐表和缺失菜单，保留已有 `agent/*` 权限节点 ID、路由及会话。

禁用收回 Agent 文件、隐藏菜单和浮窗，CLI worker 在批次边界检查开关。禁用前应先停止或等待正在执行的调用。卸载删除菜单但保留 Agent 的历史、审批、消息、任务表和 runtime 代码备份。

安装包继续走项目原有的官网授权和文件冲突处理流程。账号、密钥、缓存、日志及上传文件不归插件所有，也不会被打包。

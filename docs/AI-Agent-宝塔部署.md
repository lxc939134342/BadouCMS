# AI Agent 安装与使用：宝塔、FPM 和 CLI

本文对应当前 `aiagent`（AI 后台助手）插件。示例站点目录是 `/www/wwwroot/badoucms`，PHP 使用 8.4；实际目录不同，请替换命令中的路径。

## 1. 先选运行方式

| 项目 | FPM 模式 | CLI 模式 |
| --- | --- | --- |
| 适合场景 | 日常对话、小量后台任务 | 批量翻译、长时间后台任务 |
| 是否需要额外进程 | 不需要 | 需要常驻 worker |
| 宝塔配置重点 | PHP、Nginx、超时与流式输出 | 在前述基础上增加 Supervisor |
| 关闭网页后的任务 | 当前批次可继续；后续批次可能等待页面再次唤醒 | worker 可以继续处理队列 |

这里的模式只影响任务队列。普通聊天、工具调用、审批和实时回答仍由网页请求及 PHP-FPM 处理；CLI worker 不会替代网站服务。队列存储在 MySQL，无需额外安装 Redis。

刚开始可用默认 FPM；需要稳定处理长任务时，切换 CLI。

## 2. 在宝塔准备网站

1. 在「软件商店」安装 Nginx、PHP 8.4 和 MySQL。Agent 当前支持 PHP 8.1—8.4，不使用 PHP 8.5。
2. 上传完整项目到 `/www/wwwroot/badoucms`，包含框架资源、根 `vendor` 和插件自己的 `vendor`。
3. 在「网站」添加 PHP 站点，网站目录选择项目根目录，运行目录选择 `/public`，PHP 版本选择 8.4。[宝塔运行目录说明](https://docs.bt.cn/user-guide/site/php/site-config/website-directory/)
4. 在「伪静态」选择 ThinkPHP。已有网站保留原先可用的规则。
5. 确认网站用户 `www` 可写 `runtime`、上传目录；安装和更新插件还需要写入对应的 `app`、`config`、`modules`、`public` 等文件。首次安装也需要写入 `.env-example` 和 `public/install.lock`。使用文件所有者权限，不要把整个站点设置成 777。
6. 新站访问 `/install.php` 完成框架安装；已经运行的网站直接进入后台，不要重装数据库。新安装前不要提前创建包含 `[DATABASE]` 的根 `.env`，当前安装器会因此拒绝安装。

在 PHP 8.4 的「设置 → 安装扩展」检查以下扩展。有些扩展默认已带，无需重复安装：

```text
pdo_mysql、curl、mbstring、zip、xmlreader、xmlwriter、dom、simplexml、xml
gd（含 FreeType）、bcmath、iconv、fileinfo
```

根框架的 Excel 库是 PhpSpreadsheet；Agent 的 Word 解析还需要 `zip`、`xmlreader`。缺少扩展时，先安装扩展再重启 PHP。

部署包已经包含依赖时，不需要再次执行 Composer。使用源码且缺少依赖时，在项目根目录安装：

```bash
cd /www/wwwroot/badoucms
/www/server/php/84/bin/php /usr/bin/composer install --no-dev --prefer-dist --no-scripts
/www/server/php/84/bin/php /usr/bin/composer --working-dir=modules/aiagent install --no-dev --prefer-dist --no-scripts
/www/server/php/84/bin/php think service:discover
```

`/usr/bin/composer` 是示例，先用 `command -v composer` 确认实际路径。发布时携带两份 `composer.lock`，执行 `install`，不要直接 `update`。检查扩展可执行 `/www/server/php/84/bin/php -m`。

## 3. 安装 Agent 并开始使用

1. 在后台「插件管理」安装并启用「AI 后台助手（aiagent）」。使用项目原有的插件安装与授权流程。
2. 安装会补齐 Agent 数据表和菜单，已有历史会话会保留。框架须包含 `admin_body_end` 钩子和插件 `config` 迁移支持；当前项目已具备。
3. 在插件管理或 Agent 的账号入口登录 BadouCMS 官网账号，确认 AI 积分可用。后台管理员登录和官网账号登录是两件事。
4. 进入「AI 后台助手」或打开浮窗，先测试“查询已安装插件”等只读任务，再测试文章创建。需要保存的操作按页面审批流程执行。
5. 使用 CMS 文章、语言翻译等功能时，需要启用 CMS 等对应插件；Agent 功能入口会按插件状态和管理员权限显示。

启用状态下，配置文件在项目根目录 `config/agent.php`。禁用后，配置会随代码收回插件目录用于打包；不要在禁用期间继续启动 worker。

## 4. FPM 模式：不配置守护进程

保留 `config/agent.php` 中的队列配置：

```php
'queue' => [
    'mode' => 'fpm',
    'fpm_max_seconds' => 60,
    'fpm_max_batches' => 10,
],
```

只修改原文件的 `queue` 部分，不要删掉 `stream_tools`、`patch` 等其他配置。

任务提交后，程序先输出对话结果，再在同一 FPM 进程处理任务。到批次边界后会让出进程；Agent 页面轮询会唤醒已批准的剩余任务。因此，批量任务时建议保持 Agent 页面打开。`60` 秒是在批次边界检查的软限制，单个批次耗时可能更长；它不会创建一个独立后台服务。

### 在宝塔调整超时和流式显示

PHP 8.4「设置 → 配置修改」（`php.ini`）可按下列值配置：

```ini
memory_limit = 256M
max_execution_time = 300
output_buffering = 0
zlib.output_compression = Off
```

在 PHP-FPM 配置的 `[www]` 池中检查：

```ini
request_terminate_timeout = 600s
request_terminate_timeout_track_finished = yes
```

修改后重载或重启 PHP。宝塔不同版本入口名称略有不同，常见 FPM 配置路径为 `/www/server/php/84/etc/php-fpm.conf`；以实际配置为准。上述设置是本项目的起始配置，FPM 进程数量还应按服务器内存调整。[PHP-FPM 超时说明](https://www.php.net/manual/en/install.fpm.configuration.php)

在该站点的 Nginx 配置中，找到现有 PHP `location`，在里面增加以下设置；不要另外复制一个同样的 PHP `location`：

```nginx
fastcgi_buffering off;
fastcgi_read_timeout 300s;
fastcgi_send_timeout 300s;
```

有些站点通过 `include enable-php-84.conf` 引入 PHP 配置，需要查看其内容，确认指令没有重复且没有被内层配置覆盖。保留原来的 `fastcgi_pass` 和伪静态规则。保存时通过宝塔配置检查，再重载 Nginx。关闭缓冲后，AI 输出可以及时到浏览器；如果还有 CDN 或反向代理，也需允许流式返回。[Nginx FastCGI 说明](https://nginx.org/en/docs/http/ngx_http_fastcgi_module.html#fastcgi_buffering)

## 5. CLI 模式：宝塔 Supervisor 常驻 worker

CLI 更适合需要离开网页后继续执行的队列任务。网站仍然使用原先的 PHP-FPM。

### 第一步：切换队列模式

将根目录 `config/agent.php` 的 `queue.mode` 改为 `cli`。其余配置保留。

### 第二步：先手动确认命令可运行

```bash
cd /www/wwwroot/badoucms
/www/server/php/84/bin/php think agent:task --help
```

命令能显示帮助后再配置守护进程。手动运行 `--watch` 会开始处理真实队列，不应拿它当只读检查。

### 第三步：在宝塔增加守护进程

在「软件商店」找到并安装 Supervisor／进程管理器，添加守护进程。各字段填写如下：[宝塔 Supervisor 操作说明](https://www.bt.cn/bbs/thread-34611-1-1.html)

| 字段 | 填写内容 |
| --- | --- |
| 名称 | `badoucms-agent` |
| 启动用户 | `www`，与网站用户一致 |
| 运行目录 | `/www/wwwroot/badoucms` |
| 启动命令 | `/www/server/php/84/bin/php /www/wwwroot/badoucms/think agent:task --watch` |
| 进程数 | 先设 `1` |
| 自动启动、自动重启 | 开启 |

如果插件支持编辑 Supervisor 配置，可使用以下内容。日志目录 `runtime` 应提前存在且允许 `www` 写入：

```ini
[program:badoucms-agent]
directory=/www/wwwroot/badoucms
command=/www/server/php/84/bin/php /www/wwwroot/badoucms/think agent:task --watch
user=www
numprocs=1
autostart=true
autorestart=true
startsecs=5
startretries=3
stopwaitsecs=300
stopasgroup=true
killasgroup=true
redirect_stderr=true
stdout_logfile=/www/wwwroot/badoucms/runtime/agent-worker.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=3
```

看到“守护进程模式启动，等待任务...”且进程持续运行后，在 Agent 页面提交一项小任务，确认日志显示“处理任务”和结束状态。

关键是使用指定的 `/www/server/php/84/bin/php`，不要只写 `php`。系统命令行 PHP 可能是另一个版本，也可能缺少扩展。网页和 worker 必须读取同一个项目目录、数据库和 `runtime/admin` 缓存；当前 worker 已自动使用后台缓存目录。

### 没有 Supervisor 时：宝塔计划任务备用方式

添加「Shell 脚本」，每分钟运行一次；命令用 `www` 执行，避免生成 root 所有的缓存。此方式有分钟级等待，只适合作为备用，不与 Supervisor 同时开启。

```bash
su -s /bin/sh www -c 'cd /www/wwwroot/badoucms && /www/server/php/84/bin/php think agent:task --once >> runtime/agent-worker.log 2>&1'
```

`--once` 处理一个任务后退出，`--id 123` 执行指定任务；它们不是 AI 聊天命令。

## 6. 日常维护与排查

| 现象 | 先检查 |
| --- | --- |
| CLI 模式一直排队 | Supervisor 是否运行、启动命令 PHP 路径、队列日志 |
| 先转圈，最后一次性显示回答 | Nginx／反向代理／CDN 的缓冲设置 |
| 504 或进程中断 | Nginx 与 FPM 超时、模型接口耗时；长任务改 CLI |
| 网页可用，worker 说未登录或没权限 | 官网账号状态、`www` 文件权限、是否同一项目和后台缓存 |
| `agent:task` 命令不存在 | Agent 是否启用、是否使用正确项目和 PHP |
| 读取 Word、Excel 失败 | 对应 PHP 扩展是否齐全、文件是否加密或损坏 |

修改队列配置、更新代码或依赖后，重启常驻 worker；按需重载 PHP-FPM。禁用或升级插件前，先等待任务完成或在页面停止任务，然后停止 Supervisor；重新启用后再启动。强制杀进程可能中断正在执行的批次。

上线前至少确认：能实时对话、能打开历史、一项小任务能完成、审批操作符合权限、数据库和上传文件有备份。不要把官网账号、密钥、真实 `.env` 打入公开插件包。

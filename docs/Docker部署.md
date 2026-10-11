# 当前 BadouCMS 框架的 Docker 部署

项目已提供根目录 `compose.yaml`，以及 `deploy/docker/` 下的 PHP 镜像、Nginx 和 PHP 配置。以下命令都在项目根目录执行。

本文沿用 [官网宝塔安装教程](https://www.badoucms.com/docs/badoucms_operation/install.html) 的程序安装流程，并说明哪些环境配置由 Docker 接管。Docker 准备运行环境，框架仍通过原来的网页安装器安装。

## 1. Docker 会准备哪些东西

| 服务 | 来源 | 用途 |
| --- | --- | --- |
| `nginx` | 官方 `nginx:stable-alpine` 镜像 | 提供网站和静态文件 |
| `php` | 基于官方 `php:8.4-fpm-bookworm` 构建 | 执行框架、AI 网页对话 |
| `mysql` | 官方 `mysql:8.4` 镜像 | 保存网站数据、Agent 会话与队列 |
| `worker`（可选） | 复用 PHP 镜像 | 以 CLI 方式消费 Agent 队列 |

不需要手工下载 PHP、MySQL、Nginx 的安装包。执行 Compose 时，Docker 会拉取所需镜像，构建 PHP 扩展，再创建容器。宿主机需要安装 Docker Engine 和 Compose 插件；首次拉取镜像、构建扩展、安装 Composer 依赖需要联网。[PHP 官方镜像说明](https://hub.docker.com/_/php)

网站文件挂载到容器内 `/var/www/html`。PHP 和 worker 使用同一份代码、上传文件、配置和 `runtime/admin`，Nginx 只读同一目录。MySQL 使用名为 `mysql-data` 的数据卷，数据库不会随普通容器重建而消失。[Docker 数据卷说明](https://docs.docker.com/compose/gettingstarted/)

### 与官网宝塔部署的区别

| 安装环节 | 官网宝塔方式 | 本文 Docker 方式 |
| --- | --- | --- |
| 运行环境 | 宝塔安装 PHP、MySQL、Nginx | Compose 拉取镜像、构建 PHP 环境 |
| 程序文件 | 下载完整包、上传并解压到站点目录 | 同样准备完整包，目录挂载到容器 |
| 网站运行目录 | 在宝塔选 `/public` | 容器 Nginx 已设为 `/var/www/html/public` |
| ThinkPHP 伪静态 | 在宝塔站点配置 | `deploy/docker/nginx.conf` 已包含路由规则 |
| PHP 扩展和函数 | 在宝塔 PHP 设置中检查 | 检查容器 PHP；宿主机 PHP 设置不影响容器 |
| 数据库信息 | 填宝塔创建的数据库信息 | 填 Compose 创建的账号，数据库地址为 `mysql` |
| 域名访问 | 宝塔站点直接处理 PHP | 宝塔站点转发到容器 `127.0.0.1:8080` |
| 程序安装及后台入口 | 网页安装，再调整后台入口 | 仍使用网页安装，安装后同样调整后台入口 |

官网教程举例使用的环境版本与本方案不同；当前项目的 Docker 配置选择 PHP 8.4 和 MySQL 8.4，同时满足 Agent 的运行要求。不要在宝塔再安装一套 PHP 来执行同一个容器站点。

框架、CMS、商城和外贸询盘的安装／升级 SQL 已在 MySQL 5.7.44、8.0.46、8.4.11 验证，修改范围和旧站处理方式见 [MySQL 5.7～8.4 升级说明](MySQL57-84升级说明.md)。这项 SQL 检查不替代完整站点的部署及业务验证。

## 2. 安装 Docker，并准备项目

在普通服务器按 [Docker 官方安装说明](https://docs.docker.com/engine/install/) 安装 Docker Engine 和 Compose。在宝塔服务器，可通过面板 Docker 功能安装管理环境。

在终端确认：

```bash
docker version
docker compose version
```

新站可按官网教程从 [官方下载页](https://www.badoucms.com/xiazai.html) 获取完整包，上传后通过宝塔「文件」解压。若下载包尚未包含本次增加的 Docker 文件，需把当前项目的 `compose.yaml` 和整个 `deploy/docker/` 目录一并上传。

解压后确认项目根目录直接包含 `think`、`composer.json`、`public`，不要让压缩包多套一层目录。保留模板、静态资源、所需插件及其依赖。只上传 Git 中的文件可能缺少被忽略的 `vendor`、插件文件、上传资源；迁移现有网站时要传完整站点副本。官网下载框架包不代表已经安装了 `aiagent` 插件，仍需通过原生插件管理安装。

建议目录为 `/www/wwwroot/badoucms`。以下是这个目录中的文件关系：

```text
badoucms/
├── compose.yaml
├── app/、config/、modules/、public/、vendor/
├── deploy/docker/
│   ├── Dockerfile
│   ├── nginx.conf
│   ├── php.ini
│   ├── php-fpm.conf
│   └── .env.example
└── docs/
```

## 3. 设置容器数据库密码和访问端口

```bash
cp deploy/docker/.env.example deploy/docker/.env
```

编辑 `deploy/docker/.env`，把两个示例密码换成不同的实际密码。数据库名和账号默认都是 `badouadmin`。

默认入口是 `127.0.0.1:8080`，适合宝塔 Nginx 做域名和 HTTPS 反向代理；MySQL 和 PHP-FPM 不开放宿主机端口。

如果是没有宝塔的独立服务器，希望直接通过服务器 IP 访问，把 `HTTP_BIND` 改成 `0.0.0.0`，按需设置 `HTTP_PORT`，并放行对应 HTTP 端口。正式域名应配置 HTTPS。

`deploy/docker/.env` 只给 Compose 使用，**不是**项目根目录 `.env`。框架仍读取根 `.env` 或 `config/database.php`。不要把这两份环境配置混在一起。

## 4. 构建 PHP 环境，准备依赖和权限

```bash
docker compose --env-file deploy/docker/.env build php
```

PHP 镜像已经配置框架和 Agent 所需的扩展、Composer、SSE 输出与超时；不需要再进入容器逐个安装扩展。官网教程要求检查的 `fileinfo` 在所选 PHP 官方镜像中默认提供。

官网教程中的“删除 putenv 函数”，应理解为从 PHP 的 `disable_functions` 列表移除 `putenv`，使其可以调用，不是删除 PHP 函数或源码。当前 Docker 配置未禁用它；如果后续自行加了禁用函数配置，应在容器 PHP 配置中调整并重建环境，修改宝塔宿主机 PHP 不会影响容器。

在 Linux 部署副本上，安装器、插件管理和 CRUD 需要写站点文件。下面的权限命令会更改挂载的站点所有者，只对上传的部署副本执行。容器网站用户是 `www-data`（默认 UID 33），与宝塔的 `www` 可能不同：

```bash
docker compose --env-file deploy/docker/.env run --rm --no-deps php mkdir -p runtime public/uploads
docker compose --env-file deploy/docker/.env run --rm --no-deps php chown -R www-data:www-data /var/www/html
```

不要设置 777。以后新增文件同样应允许容器网站用户读取，需修改的文件允许写入。Nginx 挂载站点时仍为只读。

完整部署包已有两份可用依赖时，可以跳过 Composer。源码部署且依赖缺失时，执行：

```bash
docker compose --env-file deploy/docker/.env run --rm --no-deps --user www-data php composer install --no-dev --prefer-dist --no-scripts
docker compose --env-file deploy/docker/.env run --rm --no-deps --user www-data php composer --working-dir=modules/aiagent install --no-dev --prefer-dist --no-scripts
docker compose --env-file deploy/docker/.env run --rm --no-deps --user www-data php php think service:discover
```

携带根目录和 Agent 插件各自的 `composer.lock`。使用 `install` 安装锁定版本；如果没有锁文件，应先在开发环境生成并验证后再发布。这里不自动安装或启用官网插件，也不替代插件授权流程。

## 5. 启动容器并绑定域名

```bash
docker compose --env-file deploy/docker/.env up -d mysql php nginx
docker compose --env-file deploy/docker/.env ps
```

PHP 会等待 MySQL 健康检查通过后启动；MySQL 首次初始化可能需要一段时间。[Compose 启动顺序说明](https://docs.docker.com/compose/how-tos/startup-order/)

启动后可按官网要求检查 PHP 环境：

```bash
docker compose --env-file deploy/docker/.env exec php php --ri fileinfo
docker compose --env-file deploy/docker/.env exec php php -r 'echo function_exists("putenv") ? "putenv 可用\n" : "putenv 被禁用\n";'
```

### 在宝塔绑定域名和 HTTPS

1. 按官网的“新建站点”步骤绑定域名，这个站点用于反向代理；站点 PHP 版本选择「纯静态」，不再次处理项目 PHP。
2. 在该站点「反向代理」设置目标为 `http://127.0.0.1:8080`，发送域名／Host 使用当前访问域名。如果第 3 节改了 `HTTP_PORT`，这里同步使用实际端口。
3. 在「SSL」配置证书并按需强制 HTTPS。
4. 在代理的 `location` 内检查流式输出与超时：

```nginx
proxy_buffering off;
proxy_cache off;
proxy_read_timeout 300s;
proxy_send_timeout 300s;
```

保留宝塔生成的代理头，包括 Host 和协议转发头。默认不开启 CDN 对 AI 接口的缓存。[宝塔反向代理说明](https://docs.bt.cn/user-guide/site/php/site-config/reverse-proxy/)

宝塔的 Nginx 提供域名和 HTTPS，容器的 Nginx 处理站点文件和 PHP 转发。因此这里的宝塔代理站点不需要再设置项目 `/public` 运行目录或 ThinkPHP 伪静态；这两项已经在容器 Nginx 中配置。

若没有使用宝塔，且已按第 3 节开放入口，可通过 `http://服务器IP:HTTP_PORT` 访问。不要把自己电脑的 `127.0.0.1:8080` 当作远程服务器地址。

## 6. 完成框架安装或迁移

### 新安装的网站

根目录不要预先放含数据库配置的 `.env`，也不要放 `public/install.lock`。当前安装器检查到它们会认为站点已配置或已安装。

绑定域名后，访问 `https://你的域名/install.php`，继续官网教程中的网页安装步骤。填写站点名称、管理员用户名与密码；数据库填写如下：

| 安装项 | 值 |
| --- | --- |
| 数据库地址 | `mysql`（Compose 服务名） |
| 端口 | `3306` |
| 数据库名 | `deploy/docker/.env` 的 `MYSQL_DATABASE` |
| 用户 | `MYSQL_USER`，默认 `badouadmin` |
| 密码 | `MYSQL_PASSWORD` |
| 表前缀 | 默认 `bd_` |

不要填 `127.0.0.1`：在 PHP 容器里，它表示 PHP 容器自己，不是数据库容器。MySQL 镜像会先创建指定的数据库和普通用户。[MySQL 镜像初始化说明](https://hub.docker.com/_/mysql)

当前安装器会写入 `config/database.php` 和 `.env-example`；安装成功后检查调试关闭，再按需从更新后的 `.env-example` 创建根 `.env`。进入 `/admin.php`，使用安装时设置的管理员账号登录。

### 安装完成后的操作

1. 按官网教程要求修改后台入口：在宿主机项目的 `public/` 中，将 `admin.php` 改为自定义的 `.php` 文件名；也可通过插件市场的「后台地址修改」插件处理。改名后使用新地址登录。容器挂载同一目录，修改会同步生效；现有 Nginx 规则支持其他 `.php` 入口名，无需写死 `admin.php`。
2. 通过插件管理安装／启用 CMS、所需模板和业务插件。框架安装器不等于已经安装 CMS；迁移已有网站时保留原先的插件、模板和后台入口。
3. 安装／启用 `aiagent`，按 [Agent 宝塔部署说明](AI-Agent-宝塔部署.md) 登录官网账号并测试，再按第 7 节选择任务执行方式。
4. 确认自定义后台地址、前台页面、文件上传均能访问；测试一个只读 AI 问答和一项小任务。保留安装后生成的 `public/install.lock`。

以上程序安装步骤沿用 [官网安装教程](https://www.badoucms.com/docs/badoucms_operation/install.html)，容器部署主要改变运行环境和数据库连接地址。

### 迁移已经运行的网站

保留已有 `public/install.lock`，**不要进入安装器重新安装**。先导入现有数据库备份到容器 MySQL，再将根 `.env` 的 `[DATABASE]` 地址改为 `mysql`，账号密码改成容器配置。没有根 `.env` 的站点调整 `config/database.php`。域名、上传文件和所需插件一并迁移。

示例导入命令，`backup.sql` 是你从旧数据库导出的文件：

```bash
docker compose --env-file deploy/docker/.env exec -T mysql sh -c 'MYSQL_PWD="$MYSQL_PASSWORD" mysql -u "$MYSQL_USER" "$MYSQL_DATABASE"' < backup.sql
```

## 7. Agent 使用 FPM 或 CLI

FPM：保留根 `config/agent.php` 的 `queue.mode = fpm`，只启动上面的三个网站服务即可。

CLI：先确认 Agent 和对应任务插件已启用，将根 `config/agent.php` 的 `queue.mode` 改为 `cli`，然后启动 worker：

```bash
docker compose --env-file deploy/docker/.env --profile cli up -d worker
docker compose --env-file deploy/docker/.env logs --tail=100 worker
```

worker 使用同一 PHP 镜像、项目目录和数据库，运行命令为 `php think agent:task --watch`，由 Docker 自动重启进程；容器内不再安装 Supervisor。这里使用 Compose 的可选服务配置。[Compose profiles 说明](https://docs.docker.com/compose/how-tos/profiles/)

回到 FPM 前先等待任务完成或在页面停止，再停止 worker：

```bash
docker compose --env-file deploy/docker/.env stop worker
```

随后把 `queue.mode` 改回 `fpm`。改配置或更新代码后重启常驻 worker；更新 PHP 镜像时应重建 PHP 和 worker 两个服务。

重建 PHP 容器后，同时重启 Nginx，让它重新解析 PHP 容器地址：`docker compose --env-file deploy/docker/.env restart nginx`。

## 8. 日常命令与备份

```bash
# 查看网站日志
docker compose --env-file deploy/docker/.env logs --tail=100 nginx php

# 检查 PHP 扩展和依赖
docker compose --env-file deploy/docker/.env exec php php -m
docker compose --env-file deploy/docker/.env exec --user www-data php composer check-platform-reqs --no-dev
docker compose --env-file deploy/docker/.env exec --user www-data php composer --working-dir=modules/aiagent check-platform-reqs --no-dev

# 配置或代码更新后，重启已启动的 worker
docker compose --env-file deploy/docker/.env restart worker

# 导出数据库到宿主机
docker compose --env-file deploy/docker/.env exec -T mysql sh -c 'MYSQL_PWD="$MYSQL_PASSWORD" mysqldump -u "$MYSQL_USER" --single-transaction --no-tablespaces "$MYSQL_DATABASE"' > backup.sql
```

还应备份站点代码、`public/uploads`、模板、插件、根 `.env` 和 `config`。备份文件不要放在 `public` 中。

`docker compose down` 默认保留数据库数据卷；不要随意加 `-v`，它会删除本部署的数据卷。首次初始化后，仅修改环境文件的 MySQL 密码不会自动修改数据库中已有用户的密码，需要同时在数据库中改密码并更新框架连接配置。容器停止不代表数据库备份。

当前配置是单机部署方案，默认 FPM 进程上限为 4；可按实际内存和请求量调整 `deploy/docker/php-fpm.conf`。镜像标签用于跟踪维护版本，正式发布可在验证后固定镜像 digest。

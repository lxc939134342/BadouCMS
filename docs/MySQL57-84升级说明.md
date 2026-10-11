# 框架、CMS、商城、外贸 SQL 的 MySQL 5.7～8.4 兼容说明

更新日期：2026-10-11。本次兼容范围是 MySQL 5.7、8.0、8.4，不包含 MySQL 5.0～5.6。

## 修改范围

| 组件 | SQL 文件 |
| --- | --- |
| 框架 | `extend/badou/install/badouadmin-install.sql` |
| 框架升级插件 | `modules/upgrade/install.sql`、`modules/upgrade/up/extend/badou/install/badouadmin-install.sql` |
| CMS | `modules/cms/install.sql`、`modules/cms/testdata.sql` |
| 商城 | `modules/shop/install.sql`、`modules/shop/testdata.sql`、`modules/shop/region.sql` |
| 外贸版 CMS／询盘 | `modules/inquiry/install.sql`、`modules/inquiry/testdata.sql` |

本次共调整 10 个 SQL 文件。其他插件不在这次整改范围内。

## 具体改动

- 新表统一显式声明 `utf8mb4_unicode_ci`，避免相同 SQL 在不同 MySQL 版本下创建不同的排序规则；旧的 `utf8` 新表定义改为 `utf8mb4`。
- InnoDB 表显式声明 `ROW_FORMAT=DYNAMIC`，减少旧环境默认行格式带来的索引长度问题。原有存储引擎和字段类型保留。
- 安装脚本中的历史升级语句先检查字段或索引是否存在，再执行添加、删除操作。新站安装和旧站重复升级均可执行，不再依赖导入器忽略报错。
- 外贸扩展字段分别检查，支持只缺少部分字段的旧结构；先补齐 `aucode`，再添加依赖它的 `custom_tpl`。
- 商城地址的区县合并、地区排序转换仅在对应旧字段存在时执行，避免重复升级再次转换数据。
- 插件初始化数据和商城地区数据使用 `INSERT IGNORE`，保持已有主键记录和原生插件导入器的行为。
- 商城测试数据保留调用方的严格 SQL 模式，仅临时增加导入零值主键需要的设置，结束后恢复。

兼容判断使用 `information_schema` 和会话级 `PREPARE`，不需要存储过程，也不需要客户端支持 `DELIMITER`；仍通过框架原生 SQL 导入器执行。

## 实际验证结果

| 数据库版本 | 结果 |
| --- | --- |
| MySQL 5.7.44 | 632 项检查通过 |
| MySQL 8.0.46 | 632 项检查通过 |
| MySQL 8.4.11 | 632 项检查通过 |

测试覆盖框架安装和升级包安装、CMS／外贸／商城安装、旧结构补齐、重复升级、商城地区重复导入、旧地址迁移、测试数据导入，以及表排序规则和 InnoDB 行格式。使用自定义表前缀，验证了前缀替换。

测试会话启用 `ONLY_FULL_GROUP_BY`、`STRICT_TRANS_TABLES` 等严格模式。插件通过真实的 `badou\Server::importsql()` 严格模式导入，任何 SQL 执行错误都会使测试失败。另有商城原生升级流程的 16 项检查通过。

MySQL 8.0、8.4 使用独立的官方 Docker 镜像实例；所有版本均使用随机临时数据库，测试后删除。没有修改现有业务数据库。

这验证的是上述 SQL 和对应升级流程，不代表支付、订单、页面等所有业务接口已完成三个版本的回归测试，也不保证所有早期小版本或自定义 InnoDB 配置。

## 安装与升级怎么用

新站继续使用框架网页安装器、原生插件管理。已有站点继续走原生插件升级流程，不需要重新导入框架基础安装 SQL；基础安装 SQL 和 `testdata.sql` 含有重建表操作，用于新站或测试环境。

本次修改新表定义及历史结构迁移，没有自动转换已有业务表的字符集和排序规则。`CREATE TABLE IF NOT EXISTS` 会保留已有表。若旧站需要统一排序规则，应另行检查唯一键数据和字符比较行为后迁移。

本次也没有升级数据库服务器。部署 MySQL 8.4 可参考 [Docker 部署说明](Docker部署.md)；已有 5.7 服务升级到 8.4 时，数据库版本迁移需单独安排。

## 重新运行检查

在项目根目录执行：

```bash
php tests/database/mysql-compatibility.php
php tests/shop/plugin-upgrade.php
```

SQL 检查默认读取根目录数据库配置，仅允许连接本机，并要求账号具备创建和删除临时数据库的权限。它不会初始化站点插件，也不会向真实业务库写入数据。

测试独立实例可提供一个私有 JSON 连接文件，覆盖 `hostname`、`hostport`、`username`、`password`：

```bash
php tests/database/mysql-compatibility.php --connection=/path/to/private-mysql-connection.json
```

改造前的全项目检查记录见 [MySQL 兼容性检查](MySQL兼容性检查.md)。

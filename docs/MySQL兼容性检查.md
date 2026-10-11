# 改造前：项目 MySQL 5.7～8.4 的 SQL 兼容性检查

检查日期：2026-10-11。

本页保留改造前的全项目检查记录。框架、CMS、商城、外贸询盘已完成 SQL 兼容整改，并在 MySQL 5.7.44、8.0.46、8.4.11 实测通过；最新范围和结果见 [MySQL 5.7～8.4 升级说明](MySQL57-84升级说明.md)。下文的错误数量和待办属于改造前状态，其他插件仍需单独整理。

改造前结论：框架和 Agent 的安装 SQL 采用双方支持的语法，MySQL 5.7.44 实测通过。但整个项目还不能标记为“所有 SQL 在 5.7～8.4 下无条件兼容”：部分插件混用了安装和历史升级语句，部分表依赖默认排序规则和 InnoDB 行格式。该轮检查尚未完成 MySQL 8.0、8.4 的实际导入及业务测试。

## 检查范围与验证方式

检查框架安装脚本、升级包中的框架脚本，以及当前插件的安装、升级、地区和测试数据 SQL，共 19 个文件。未包含第三方 vendor 内的脚本、线上数据库自行增加的表，以及用户以后通过建表工具生成的 SQL。

实际数据库版本为 MySQL 5.7.44，InnoDB 页大小为 16 KB，默认行格式为 DYNAMIC。检查使用随机命名的临时数据库，结束后已删除，没有修改现有业务数据库。

在包含 `ONLY_FULL_GROUP_BY`、`STRICT_TRANS_TABLES`、`NO_ZERO_IN_DATE`、`NO_ZERO_DATE`、`ERROR_FOR_DIVISION_BY_ZERO`、`NO_ENGINE_SUBSTITUTION` 的会话中逐条执行 SQL。文件显式设置 SQL_MODE 时沿用文件设置；商城测试数据会主动放宽模式，因此其通过结果不能当作全程严格模式测试。

插件脚本所需的框架表预先建在临时库中，VIP 测试数据、商城地区数据和迁移脚本还预置对应依赖。检查保留原始 INSERT；原生插件导入器实际会改为 INSERT IGNORE。

共执行 2,160 条语句，其中 235 条 CREATE TABLE 均未出现语法、类型或索引长度错误。数量包含不同脚本中重复的建表语句，不代表有 235 张不同的业务表。

## MySQL 5.7.44 实测结果

| 脚本 | 结果 |
| --- | --- |
| `extend/badou/install/badouadmin-install.sql` | 289 条语句，无执行错误 |
| 升级包中的 `badouadmin-install.sql` | 279 条语句，无执行错误 |
| `modules/aiagent/install.sql` | 5 张表全部创建成功 |
| AI 智能创作、可视化编辑器、JS 翻译安装脚本 | 无执行错误 |
| CMS、询盘、商城、Geo 模板测试数据及商城地区数据 | 预置必要依赖后，无执行错误 |
| CMS 安装脚本 | 6 处重复添加字段或索引 |
| 询盘安装脚本 | 8 处重复添加字段或索引 |
| 商城安装脚本 | 14 处重复加字段，或旧字段已经不存在 |
| VIP 安装脚本 | `user.openid` 已存在，重复添加 |
| 升级插件安装脚本 | 6 处重复主键数据、1 处重复添加字段 |
| PbootCMS 迁移辅助 SQL | 在当前 CMS 结构上重复添加 `acode` |
| VIP 测试数据 | 预置 VIP 表后，无执行错误 |

`DROP TABLE IF EXISTS` 在空库中产生的“表不存在”提示不属于失败。

上述插件错误属于脚本重复执行和新旧表结构混用，不是 MySQL 5.7 或 8.4 特有的问题。例如 CMS 的建表语句已定义 `content_aucode` 索引，文件末尾又添加一次；商城新表已移除 `district`，历史升级语句仍读取、删除它。

当前 `extend/badou/Server.php::importsql()` 默认捕获并跳过 PDO 错误，将 INSERT 改为 INSERT IGNORE。因此原生安装流程能继续；直接用数据库工具完整执行这些文件，或使用严格导入模式，会遇到错误。安装页面没有报错，不能据此认定每条 SQL 都成功了。

## 跨版本需要注意的地方

### 1. 显式指定排序规则

很多表只声明 `DEFAULT CHARSET=utf8mb4`，另一些表显式声明 `utf8mb4_unicode_ci`，建表工具还使用 `utf8mb4_general_ci`。

MySQL 5.7 的 utf8mb4 默认排序规则为 `utf8mb4_general_ci`，8.0／8.4 通常为 `utf8mb4_0900_ai_ci`。只指定字符集的建表语句使用该字符集默认排序规则，不能仅靠数据库或 `--collation-server` 设置保证所有表统一。[MySQL 默认排序规则说明](https://dev.mysql.com/doc/refman/8.4/en/server-system-variables.html#sysvar_default_collation_for_utf8mb4)、[表字符集选择规则](https://dev.mysql.com/doc/refman/8.4/en/charset-table.html)

这意味着相同 SQL 在两个版本下可能创建不同的表；从 8.4 导出的结构也可能携带 5.7 不认识的 `0900` 排序规则。

建议在新建表 SQL 中明确使用双方支持的规则，例如 `DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`。已有表改排序规则前，需要检查唯一键数据和文本比较行为，不能直接批量替换线上表。

### 2. 可视化编辑器依赖大索引支持

`modules/vieweditor/install.sql` 的联合唯一键包含 `theme(100)`、`tpl_path(255)`、`node_key(120)`，utf8mb4 最大索引字符数据长度为 1,900 字节。

实测默认 DYNAMIC 行格式创建成功；单独把这张临时表改为 COMPACT 后，MySQL 5.7.44 报错 1071，提示索引上限 767 字节。建议部署时使用 DYNAMIC、开启大索引支持，并保留通常的 16 KB InnoDB 页大小；旧服务器配置需要检查。[MySQL 5.7 InnoDB 索引限制](https://docs.oracle.com/cd/E17952_01/mysql-5.7-en/innodb-limits.html)

Agent 的 `agent_workflow` 两列分别是 ASCII varchar(510)，本次 DYNAMIC 和 COMPACT 检查都创建成功，不应把联合键总长度简单相加后误判为必然失败。

### 3. 旧写法仍能使用，但需要逐步整理

项目中的 `int(10)`、`bigint(20)` 是显示宽度，MySQL 8.4 仍支持，属于弃用写法，不是当前导入失败的原因。部分旧表和字段使用 `utf8`，在 8.4 中仍对应 utf8mb3，也已弃用；它与 utf8mb4 能保存的字符范围不同。[整数显示宽度说明](https://dev.mysql.com/doc/refman/8.4/en/numeric-type-attributes.html)、[utf8 别名说明](https://dev.mysql.com/doc/refman/8.4/en/charset-unicode-utf8.html)

Agent 队列索引中的 `priority DESC` 在 5.7 可以导入，但降序定义被忽略；8.0／8.4 支持实际降序索引。查询结果由 ORDER BY 决定，差异主要在查询执行效率。[降序索引说明](https://dev.mysql.com/doc/refman/8.4/en/descending-indexes.html)

现有 InnoDB 和 MyISAM 建表语句在本次 5.7 检查中都能执行。MyISAM 的事务限制属于存储引擎行为，不能把“SQL 能导入”理解为所有业务写入都支持回滚。

### 4. MySQL 8.4 的连接认证与 SQL 分开检查

MySQL 8.4 默认禁用 `mysql_native_password`。迁移旧账号时，可能在运行安装 SQL 之前就连接失败；建议按新环境创建应用数据库账号并验证 PHP PDO 连接。[MySQL 8.4 认证说明](https://dev.mysql.com/doc/refman/8.4/en/native-pluggable-authentication.html)

## 后续验证与整理建议

优先给新表显式指定兼容排序规则、整理插件安装与升级语句，并在可视化编辑器建表中明确 DYNAMIC。此次只做检查，没有修改项目 SQL 或现有数据库结构。

当时建议随后使用独立 MySQL 8.0、8.4 实例验证网页安装、原生插件安装／升级、测试数据导入和主要业务查询。该轮检查时本机 Docker 服务未启动，未实际运行这两个版本；静态检查未发现明确的 8.4 不支持语法，不能替代实际版本测试，也不代表 MySQL 5.7 所有早期小版本和自定义配置均已验证。

---
name: badoucms-plugin
description: 开发、安装或修改 BadouCMS 模块插件时使用，按当前项目模块结构组织路由、控制器、模型、配置和安装升级逻辑。
---

# BadouCMS 插件开发

用于 `modules/<插件名>/` 下的功能闭环开发；不修改 `app/` 核心文件，不把业务 SQL 放进模板。

## 开发流程

1. 先读 `docs/BADOUCMS_PLUGIN_SPEC.md`，再选一个同类型现有模块作结构参照：后台能力看 `alioss` 或 `cmsdataio`，前台业务看 `inquiry` 或 `shop`。
2. 模块入口是 `<Module>.php`，命名空间为 `modules\<插件名>`；实现 `AppInit()`，按需实现 `enable()`、`disable()`、`install()`、`upgrade()`、`uninstall()`。
3. 控制器放在模块内 `app/admin/controller/`、`app/index/controller/`、`app/api/controller/`。命名空间遵循同类模块的实际挂载方式，常见为 `app\admin\controller\<子空间>`；不要只凭记忆假设，先核对入口注册和路由。
4. 数据库使用 `think\facade\Db` 或模型，动态读取表前缀；安装脚本使用 `__PREFIX__` 占位。字段名用 snake_case。
5. 配置写在模块 `config.php`，基础信息写在 `info.ini`；菜单、配置和升级逻辑必须可重复执行或在升级时判断已存在。
6. 前台模板放在模块 `template/`，公开资源放在 `public/modules/<插件名>/`；后台 UI 使用现有 Layui 组件。
7. 修改函数、类或方法前运行 GitNexus 影响分析；提交前运行 `npx gitnexus detect-changes`。

## 安全边界

- 只在模块目录内实现业务；扩展 CMS 行为优先使用事件、观察者、路由注册或模板标签。
- 接收输入先白名单校验；数据库查询使用参数绑定或查询构建器，不拼接用户输入。
- `try` 块内不要调用会抛出响应异常的 `$this->success()` / `$this->error()`；在 `try` 外统一返回。
- 后台接口检查权限；前台接口明确 `noNeedLogin` / `noNeedRight`，公开接口只返回必要字段。
- 卸载逻辑必须说明是否删除数据；默认保留业务数据，删除前必须有安装声明或用户确认。

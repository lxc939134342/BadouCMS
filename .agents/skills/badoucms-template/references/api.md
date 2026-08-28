# 远程 API 数据

`$bd.api_data` 包含通过 `apiSecret()` 返回的额外数据，可被模块插件通过 `BeforeAssignBd` 钩子注入。

模块插件如果需要向前台暴露 API 数据，应在观察者中合并到 `$bdassign` 数组，模板通过 `$bd.<字段名>` 访问。

# 分页、筛选和搜索

原始来源：`pagebar.md`、`selectall.md`、`search.md`、`list.md`。默认模板证据：`productlist.html`、`search.html`。

分页变量属于最后一个开启分页的查询。分页 HTML 必须使用 `{$page.bar |raw}`，否则会被转义。

`selectall` 和 `select` 用于扩展字段筛选：二者都需要 `field`；`selectall` 可带 `text`、`class`、`active`。`select` 字段为 `current`、`i`、`link`、`value`。先在后台把扩展字段配置成单选/多选并填写选项。

搜索表单提交到 `$bd.scaction`，可传 `keyword`、`scode`、`field`（例如 `title|content`）及任意字段。保留参数不能作为任意字段：`page`、`start`、`lfield`、`keyword`、`fuzzy`、`scode`、`lg`、`searchtpl`、`field`、`num`。

当前 `search` 标签可用参数是 `num`、`page`、`order`、`filter`、`tags`、`fuzzy`、`start`。文档中列出的 `lfield`、`field`、`istop`、`isrecommend`、`isheadline`、`ispics`、`isico` 没有被当前 `Bd.php` 转发，不能写到标签上。旧模板里的 `limit` 也不会被当前实现转发；限制数量应写 `num`。`page=false` 时 `start` 才是偏移量。

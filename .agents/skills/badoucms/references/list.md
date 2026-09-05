# 内容列表标签 `list`

原始来源：`list.md`。用于输出文章/产品等内容列表。

## 参数

- `scode`：栏目编码。在非列表页必填；列表页中省略则表示当前栏目并默认开启分页。
- `num`：每页数量。
- `order`：排序字段，如 `id desc`、`istop desc, id desc`。
- `filter`：过滤条件，如 `istop=1`。
- `tags`：标签 ID 逗号列表，多个标签任一命中。
- `fuzzy`：是否模糊搜索标题。
- `page`：是否开启分页，默认列表页 `true`、非列表页 `false`。
- `start`：`page=false` 时的偏移量。
- `isico`、`ispics`、`istop`、`isrecommend`、`isheadline`：按标记筛选。
- `alias`：循环变量名，默认 `list`。
- `empty`：无数据时显示的内容。
- `key`：循环计数变量名，默认 `i`。
- `mod`：奇偶数模式，默认 `2`。

## 字段

`id`、`scode`、`subscode`、`title`、`titlecolor`、`subtitle`、`author`、`source`、`outlink`、`date`、`ico`、`pics`、`content`、`tags`、`istop`、`isrecommend`、`isheadline`、`visits`、`likes`、`oppose`、`create_user`、`update_user`、`update_time`、`sortlink`、`sortname`。

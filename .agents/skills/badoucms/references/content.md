# 内容详情标签 `content`

原始来源：`content.md`。用于在非详情页中调用指定内容，或在详情页中使用当前内容。

## 参数

- `id`：内容 ID。与 `scode` 二选一。
- `scode`：栏目编码，获取该栏目最新一条。与 `id` 二选一。
- `alias`：变量名，默认 `content`。
- `empty`：无数据时显示的内容。

## 字段

与 `list` 标签的字段相同，另含 `content`（完整内容 HTML）、`contenttext`（纯文本）。

在详情页中不需要写 `content` 标签，模板直接使用 `$content.xxx`。

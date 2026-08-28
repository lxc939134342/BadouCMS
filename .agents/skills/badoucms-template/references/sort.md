# 栏目标签 `sort`

原始来源：`sort.md`。用于输出栏目树或子栏目列表。

## 参数

- `scode`：栏目编码，可以是单个、逗号列表或变量。与 `aucode` 二选一。
- `aucode`：栏目别名编码。与 `scode` 二选一。
- `alias`：循环变量名，默认 `sort`。
- `empty`：无数据时显示的内容。
- `key`：循环计数变量名，默认 `i`。
- `mod`：奇偶数模式，默认 `2`。

## 字段

`scode`、`name`、`title`、`keywords`、`description`、`pic`、`link`、`target`、`parent_id`、`child`（是否有子栏目）、`childlist`（子栏目数组）。

默认模板证据：`template/cms/default/comm/nav.html`。

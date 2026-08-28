# 导航标签 `nav`

原始来源：`nav.md`。全站可用，用于 CMS 栏目树。

## 参数

- `parent`：父栏目 ID 或 `0`（默认一级）。优先级高于 `scode`。
- `scode`：栏目编码，可用逗号限定范围。
- `aucode`：栏目别名编码。与 `scode` 二选一。
- `num`：限制返回数量。
- `alias`：循环变量名，默认 `nav`。嵌套导航必须为内层指定不同 `alias`，否则循环变量会混乱。
- `empty`：无数据时显示的内容。
- `key`：循环计数变量名，默认 `i`。
- `mod`：奇偶数模式，默认 `2`。

## 字段

`scode`、`name`、`link`、`target`、`child`（是否有子栏目）、`childlist`（子栏目数组，嵌套时用内层 `nav` 读取）。

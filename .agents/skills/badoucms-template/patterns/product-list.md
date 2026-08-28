# 产品列表模式

产品列表通常包含扩展字段筛选（`selectall`/`select`）和分页。

`selectall` 需要 `field` 参数对应后台扩展字段编码。`select` 用于单选筛选。筛选参数通过 GET 传递，与搜索参数共存。

参考默认实现：`template/cms/default/productlist.html`、`skill/badoucms-template/examples/list.html`。

# 导航栏模式

公共导航放在 `template/cms/<主题>/comm/nav.html`，通过 `{include file="comm/nav" /}` 引入。

使用 `nav` 标签输出栏目树，嵌套时内层必须指定不同 `alias`。

参考默认实现：`template/cms/default/comm/nav.html`。

# 新闻列表模式

列表页模板为 `template/cms/<主题>/newslist.html`。栏目页自动使用当前 `scode` 并默认开启分页。

关键标签：`list`（省略 `scode` 使用当前栏目）、`{$page.bar |raw}` 输出分页。

参考默认实现：`template/cms/default/newslist.html`、`skill/badoucms-template/examples/list.html`。

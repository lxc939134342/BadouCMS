# 内容详情模式

详情页模板为 `template/cms/<主题>/news.html`（或按栏目配置）。模板直接使用 `$content.xxx`，不需要写 `content` 标签。

常见结构：面包屑（`position`）→ 标题/日期 → 正文（`$content.content|raw`）→ 上一篇/下一篇（`nextprev.html`）→ 评论（`comment`）→ 相关内容（`list tags=$content.tags`）。

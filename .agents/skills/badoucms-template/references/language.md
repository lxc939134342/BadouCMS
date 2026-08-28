# 多语言

`$bd.sitelanguage` 返回当前语言代码（如 `cn`、`en`、`jp`）。语言切换表单提交到 `$bd.lgpath`（即 `/do/area`）。

模板中按语言条件渲染：

```
{if $bd.sitelanguage == 'cn'}中文{elseif $bd.sitelanguage == 'en'}English{else /}Other{/if}
```

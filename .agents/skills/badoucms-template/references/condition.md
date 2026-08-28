# 条件判断

使用 ThinkPHP 模板条件语法。常用写法：

```
{if condition}
{elseif condition /}
{else /}
{/if}
{empty name="var"}...{/empty}
{notempty name="var"}...{/notempty}
{present name="var"}...{/present}
```

条件中可直接使用模板变量和 PHP 函数，例如 `{if $list.i == 1}`、`{if !empty($content.pics)}`。

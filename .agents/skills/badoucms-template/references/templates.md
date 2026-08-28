# 主题与模板解析

原始来源：官方模板文档。默认模板证据：`template/cms/default/`。

主题根目录优先使用 `template/cms/<主题>/`；不存在时回退到 `public/template/cms/<主题>/<tpl_html_dir>/`，其中 `<tpl_html_dir>` 来自系统配置，默认 `html`。资源地址 `$bd.sitetplpath` 始终指向 `/template/cms/<主题>`，因此公开资源应放在主题下的公开目录并在模板中用 `$bd.sitetplpath` 引用。

## 公共目录兼容层

`template/cms/<主题>/comm/` 下的文件可通过 `{include file="comm/xxx" /}` 引用。如果主题根目录下不存在同名文件，会自动回退到 `comm/` 目录查找。

## WAP 子目录

开启 WAP 后，系统优先查找 `template/cms/<主题>/wap/<页面>.html`，不存在则回退到 `template/cms/<主题>/<页面>.html`。

## 可切换模板

- `searchtpl`：搜索结果页模板名，通过搜索表单参数传递，默认 `search.html`。
- `tagstpl`：标签聚合页模板名，通过标签链接参数传递，默认 `tags.html`。
- `custom_tpl`：自定义模板名，通过 URL 参数传递，可指向主题内任意 `.html` 文件。

以上均只接受文件名（不含扩展名），不能包含路径分隔符，防止目录穿越。

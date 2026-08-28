---
name: badoucms-template
description: 开发或修改 BadouCMS 前台模板时使用，依据官方标签文档、当前标签库和默认模板安全选择数据与页面结构。
---

# BadouCMS 前台模板开发

用于 `template/cms/<主题>/` 下的前台页面；不用于后台 Layui 组件，也不在模板中直接访问数据库。

## 工作流程

1. 先读 `references/templates.md` 确认主题根目录、公共目录兼容层、WAP 子目录与 `searchtpl`、`tagstpl`、`custom_tpl` 的解析规则。
2. 确定页面类型（首页/列表/详情/单页/搜索/标签/用户中心），阅读对应的 `patterns/` 场景文件。
3. 按数据用途选择标签，查阅对应 `references/` 文件确认可用参数和字段。
4. 只读取当前任务需要的 `references/` 文件。按数据用途选择当前上下文、明确指定栏目/内容，或全站数据，避免多余查询。
5. 使用 `{$page.bar |raw}` 输出分页 HTML，避免被转义。
6. 只使用 `scripts/tag-contracts.json` 与 reference 中有来源的标签、参数与字段。文档和当前 `modules/cms/taglib/Bd.php` 不一致时，以 reference 标出的当前实现限制为准。
7. 运行 `node scripts/check-skill-sync.js` 确认技能契约与实现一致，再运行 `node scripts/validate-template.js <模板文件...>`；最后对照默认主题中相同场景的页面。

## 参考文档索引

- 全局变量、站点/公司信息、链接和工具：`references/global.md`
- 主题解析、入口模板和可切换模板：`references/templates.md`
- 栏目和导航：`references/sort.md`、`references/nav.md`
- 内容列表、详情和上一篇/下一篇：`references/list.md`、`references/content.md`
- 幻灯片和友情链接：`references/slide.md`
- 选项筛选、搜索与分页：`references/page.md`
- 留言、评论和自定义表单：`references/form.md`
- 多语言、条件、远程 API：`references/language.md`、`condition.md`、`api.md`
- 内容标签：`references/tags.md`
- 图片集：`references/pics.md`

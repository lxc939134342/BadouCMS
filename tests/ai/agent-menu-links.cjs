const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const html = fs.readFileSync(path.join(__dirname, '../../app/admin/view/agent/index.html'), 'utf8');
const script = html.match(/<script>([\s\S]*?)<\/script>/)[1];
new vm.Script(script);

function pageFunction(name) {
  const start = script.search(new RegExp('    function ' + name + '\\('));
  assert.notEqual(start, -1);
  const rest = script.slice(start);
  const end = rest.slice(1).search(/\n    (?:async )?function /);
  return end < 0 ? rest : rest.slice(0, end + 1);
}

const menus = [
  { 'menu-url': 'cms.site', 'menu-id': '11', 'menu-title': '站点配置' },
  { 'menu-url': 'cms.label/content', 'menu-id': '12', 'menu-title': '定制标签' },
  { 'menu-url': 'cms.content?type=article&lang=cn', 'menu-id': '13', 'menu-title': '文章 "标题" & <内容>' },
];

class Query {
  constructor(nodes) { this.nodes = nodes; this.length = nodes.length; }
  attr(name) { return this.nodes[0]?.[name]; }
  data(name) { return this.attr('data-' + name); }
  text() { return this.nodes[0]?.text || ''; }
  hasClass() { return false; }
  find(selector) {
    if (selector === 'a[menu-url]') return new Query(menus);
    const url = selector.match(/^a\[menu-url="([^"]+)"\]$/)?.[1];
    return new Query(menus.filter(menu => menu['menu-url'] === url));
  }
  filter(callback) { return new Query(this.nodes.filter(node => callback.call(node))); }
  first() { return new Query(this.nodes.slice(0, 1)); }
}

function $(node) { return new Query(node === '#sideMenu' ? [{}] : [node]); }
const jumps = [];
const context = { $, top: { $, layui: { admin: { jump: (...args) => jumps.push(args) } } } };
vm.createContext(context);
vm.runInContext(['escapeHtml', 'menuLinkTitle', 'inlineMarkdown'].map(pageFunction).join('\n'), context);

// 使用真实框架的点击处理，验证英文链接文字不会成为标签页标题。
const indexScript = fs.readFileSync(path.join(__dirname, '../../public/assets/libs/badouadmin/index.js'), 'utf8');
const handler = indexScript.match(/on\("click", "\.btn-addtab", function \(e\) \{([\s\S]*?)\n  \}\);/);
assert.ok(handler);
const click = vm.runInContext('(function(e) {' + handler[1] + '\n})', context);
for (const menu of menus.slice(0, 2)) {
  const rendered = context.inlineMarkdown('[' + menu['menu-url'] + '](' + menu['menu-url'] + ')');
  assert.ok(rendered.includes('title="' + menu['menu-title'] + '"'));
  assert.ok(rendered.endsWith('>' + menu['menu-title'] + '</a>'));
  click.call({ href: menu['menu-url'], title: menu['menu-title'], text: menu['menu-title'] }, {});
  assert.deepEqual(jumps.at(-1), [menu['menu-id'], menu['menu-title'], menu['menu-url']]);
}

const escaped = context.inlineMarkdown('[入口](cms.content?type=article&amp;lang=cn)');
assert.ok(escaped.includes('title="文章 &quot;标题&quot; &amp; &lt;内容&gt;"'));
assert.ok(!escaped.includes('<内容>'));
assert.ok(context.inlineMarkdown('[官网](https://example.com)').includes('target="_blank"'));
assert.ok(!context.inlineMarkdown('[危险](javascript:alert)').includes('<a '));
context.top.$ = undefined;
assert.equal(context.inlineMarkdown('[内容管理](cms.content)'),
  '<a href="cms.content" class="btn-addtab" title="内容管理">内容管理</a>');

console.log('Agent 菜单名称、标签页标题和菜单 ID 复用、标题转义及链接回退检查通过');

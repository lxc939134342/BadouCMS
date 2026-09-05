#!/usr/bin/env node
const fs = require('node:fs');
const path = require('node:path');

const contracts = JSON.parse(fs.readFileSync(path.join(__dirname, 'tag-contracts.json'), 'utf8'));
const known = new Set(Object.keys(contracts));
const selfClosing = new Set(['position', 'selectall', 'form', 'qrcode']);
const paginating = new Set(['list', 'search', 'comment', 'message', 'formlist']);
const files = process.argv.slice(2);

if (!files.length) {
  console.error('用法：node scripts/validate-template.js <模板文件...>');
  process.exit(2);
}

let failed = false;
let warned = false;

function lineAt(source, index) {
  return source.slice(0, index).split('\n').length;
}

function report(messages, file, line, text) {
  messages.push(`${file}:${line}  ${text}`);
}

function parseAttrs(raw, file, line, errors) {
  const attrs = {};
  const attrPattern = /\s+([A-Za-z_][\w-]*)\s*=\s*(?:"((?:\\.|[^"\\])*)"|'((?:\\.|[^'\\])*)'|([^\s"'=<>`]+))/g;
  let consumed = raw;
  let match;
  while ((match = attrPattern.exec(raw))) {
    const name = match[1];
    const value = match[2] ?? match[3] ?? match[4] ?? '';
    if (Object.hasOwn(attrs, name)) {
      report(errors, file, line, `属性 ${name} 重复`);
    }
    attrs[name] = value;
    consumed = consumed.replace(match[0], ' ');
  }
  const leftover = consumed.replace(/\//g, '').trim();
  if (leftover) {
    report(errors, file, line, `属性格式无效或引号未闭合：${leftover}`);
  }
  return attrs;
}

function insideHtmlAttribute(source, tagStart) {
  const tagOpen = source.lastIndexOf('<', tagStart - 1);
  const tagClose = source.lastIndexOf('>', tagStart - 1);
  if (tagOpen === -1 || tagOpen < tagClose) return false;

  const context = source.slice(tagOpen, tagStart);
  return /\s+[A-Za-z_][\w:-]*\s*=\s*(?:"[^"]*|'[^']*')$/.test(context);
}

function checkContract(name, contract, attrs, file, line, errors, warnings) {
  for (const attr of Object.keys(attrs)) {
    if (!contract.attrs.includes(attr)) {
      report(warnings, file, line, `bd:${name} 当前实现不转发属性 ${attr}`);
    }
  }
  for (const attr of contract.required || []) {
    if (!(attr in attrs)) {
      report(errors, file, line, `bd:${name} 缺少必填属性 ${attr}`);
    }
  }
  for (const group of contract.oneOf || []) {
    if (!group.some((attr) => attr in attrs)) {
      report(errors, file, line, `bd:${name} 至少需要 ${group.map((attr) => `${attr}="..."`).join(' 或 ')}`);
    }
  }
}

function isTruthy(value) {
  return !['', '0', 'false', 'off', 'no'].includes(String(value).toLowerCase());
}

function checkPagination(source, file, warnings) {
  const pages = [];
  const pattern = /\{bd:([a-z]+)([^}]*)\}/g;

  for (let match; (match = pattern.exec(source));) {
    const [full, name, raw] = match;
    if (!paginating.has(name)) continue;

    const line = lineAt(source, match.index);
    const attrs = parseAttrs(raw.replace(/\/\s*$/, ''), file, line, []);
    const defaultsOn = name !== 'list' || !Object.hasOwn(attrs, 'scode');
    const enabled = Object.hasOwn(attrs, 'page') ? isTruthy(attrs.page) : defaultsOn;
    if (enabled) pages.push({ line, display: full.trim() });
  }

  if (pages.length > 1) {
    report(warnings, file, pages.at(-1).line,
      `发现 ${pages.length} 个分页查询；最终 \$page 只保留最后一个`);
  }
  if (/\{\$page\.bar(?![^}]*\|raw)/.test(source)) {
    report(warnings, file, 1, '`{$page.bar}` 应写成 `{$page.bar|raw}`，否则分页 HTML 会被转义');
  }
}

for (const file of files) {
  const source = fs.readFileSync(file, 'utf8');
  const stack = [];
  const thinkStack = [];
  const errors = [];
  const warnings = [];

  const tags = /\{(\/?)bd:([a-z]+)([^}]*)\}/g;
  for (let match; (match = tags.exec(source));) {
    const [, closing, name, raw] = match;
    const line = lineAt(source, match.index);
    const contract = contracts[name];

    if (!contract) {
      report(errors, file, line, `未知 BadouCMS 标签 bd:${name}`);
      continue;
    }

    if (closing) {
      const open = stack.pop();
      if (open !== name) {
        report(errors, file, line, `闭合标签 bd:${name} 与当前打开标签 bd:${open || '无'} 不匹配`);
      }
      continue;
    }

    const explicitSelfClosed = raw.trim().endsWith('/');
    const attrs = parseAttrs(raw, file, line, errors);
    const nativeSelfClosed = selfClosing.has(name);

    if (insideHtmlAttribute(source, match.index)) {
      checkContract(name, contract, attrs, file, line, errors, warnings);
      continue;
    }

    if (nativeSelfClosed && !explicitSelfClosed) {
      report(errors, file, line, `自闭合标签应写成 {bd:${name} ... /}`);
    }
    if (!nativeSelfClosed && !explicitSelfClosed) {
      stack.push(name);
    }

    checkContract(name, contract, attrs, file, line, errors, warnings);
  }

  if (stack.length) {
    report(errors, file, 1, `缺少闭合标签：${stack.map((name) => `bd:${name}`).join(', ')}`);
  }

  const thinkTags = /\{(\/?)(if|foreach|volist|notempty|present|empty)\b([^}]*)\}/g;
  for (let match; (match = thinkTags.exec(source));) {
    const [, closing, name, raw] = match;
    const line = lineAt(source, match.index);
    if (!closing && !/^\s/.test(raw)) continue;

    if (closing) {
      const open = thinkStack.pop();
      if (open !== name) {
        report(errors, file, line, `闭合标签 {${name}} 与当前打开标签 {${open || '无'}} 不匹配`);
      }
    } else if (!raw.trim().endsWith('/')) {
      thinkStack.push(name);
    }
  }
  if (thinkStack.length) {
    report(errors, file, 1, `缺少 ThinkPHP 闭合标签：${thinkStack.map((name) => `{/${name}}`).join(', ')}`);
  }

  checkPagination(source, file, warnings);

  for (const text of errors) {
    console.error(text);
    failed = true;
  }
  for (const text of warnings) {
    console.warn(text);
    warned = true;
  }
  if (!errors.length && !warnings.length) {
    console.log(`${path.basename(file)}：BadouCMS 标签结构通过`);
  } else if (!errors.length) {
    console.log(`${path.basename(file)}：结构通过，有 ${warnings.length} 条提示`);
  }
}

if (warned) console.warn('校验完成：有提示需要人工确认');
process.exit(failed ? 1 : 0);

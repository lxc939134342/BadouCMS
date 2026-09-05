#!/usr/bin/env node
const fs = require('node:fs');
const path = require('node:path');

const root = process.cwd();
const read = (relative) => fs.readFileSync(path.join(root, relative), 'utf8');
const bdSource = read('modules/cms/taglib/Bd.php');
const baseSource = read('app/index/controller/cms/Base.php');
const contracts = JSON.parse(fs.readFileSync(path.join(__dirname, 'tag-contracts.json'), 'utf8'));

let failed = false;
const fail = (message) => {
  console.error(message);
  failed = true;
};

const declaration = bdSource.match(/protected \$tags = \[([\s\S]*?)\];/);
if (!declaration) {
  fail('无法解析 modules/cms/taglib/Bd.php 中的 $tags 声明');
} else {
  const implemented = [...declaration[1].matchAll(/^\s*'([a-z]+)'\s*=>\s*\[/gm)].map((match) => match[1]);
  const contracted = Object.keys(contracts);
  for (const tag of implemented.filter((tag) => !contracts[tag])) {
    fail(`tag-contracts.json 缺少 Bd.php 标签：${tag}`);
  }
  for (const tag of contracted.filter((tag) => !implemented.includes(tag))) {
    fail(`tag-contracts.json 声明了未实现标签：${tag}`);
  }
}

const assignBody = baseSource.match(/protected function assignBd\(\): void\s*\{([\s\S]*?)\n    \}/);
if (!assignBody) {
  fail('无法解析 Base::assignBd() 的固定变量');
} else {
  const variables = [...assignBody[1].matchAll(/'([a-z_]+)'\s*=>/g)].map((match) => match[1]);
  const globalDoc = fs.readFileSync(path.join(__dirname, '../references/global.md'), 'utf8');
  for (const variable of variables) {
    if (!globalDoc.includes(`\`${variable}\``)) {
      fail(`references/global.md 缺少 assignBd 固定变量：${variable}`);
    }
  }
}

if (!failed) console.log('BadouCMS 技能契约与当前实现一致');
process.exit(failed ? 1 : 0);

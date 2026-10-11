const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const html = fs.readFileSync(__dirname + '/../../app/admin/view/agent/index.html', 'utf8');
const script = html.match(/<script>([\s\S]*?)<\/script>/)[1];
new vm.Script(script);

function pageFunction(name) {
  const start = script.search(new RegExp('    (?:async )?function ' + name + '\\('));
  assert.notEqual(start, -1);
  const rest = script.slice(start);
  const end = rest.slice(1).search(/\n    (?:async )?function /);
  return end < 0 ? rest : rest.slice(0, end + 1);
}

function node(tag = 'div', classes = '') {
  return { tag, classes: new Set(classes.split(/\s+/).filter(Boolean)), children: [], parent: null, value: '', data: {}, events: {}, scrollHeight: 0,
    setSelectionRange(start, end) { this.selectionStart = start; this.selectionEnd = end; } };
}
function matches(element, selector) {
  return selector.split(',').some(part => {
    part = part.trim();
    if (part === '[data-agent-view]') return element.attributes?.['data-agent-view'] !== undefined;
    return part.startsWith('.') ? part.slice(1).split('.').every(name => element.classes.has(name)) : element.tag === part;
  });
}
function descendants(element) { return element.children.flatMap(child => [child, ...descendants(child)]); }
class Selection extends Array {
  static get [Symbol.species]() { return Array; }
  text(value) { for (const element of this) element.value = value; return this; }
  html(value) { for (const element of this) element.value = value; return this; }
  empty() { for (const element of this) { element.children = []; element.value = ''; } return this; }
  append(...parts) {
    for (const part of parts) for (const element of (typeof part === 'string' ? $(part) : part)) { element.parent = this[0]; this[0].children.push(element); }
    return this;
  }
  find(selector) { return new Selection(...this.flatMap(descendants).filter(element => matches(element, selector))); }
  remove() {
    for (const element of this) element.parent.children.splice(element.parent.children.indexOf(element), 1);
    return this;
  }
  each(callback) { this.forEach(element => callback.call(element)); return this; }
  after(part) {
    for (const element of part) { element.parent = this[0].parent; element.parent.children.splice(element.parent.children.indexOf(this[0]) + 1, 0, element); }
    return this;
  }
  data(key, value) { if (value === undefined) return this[0].data[key]; this[0].data[key] = value; return this; }
  children(selector) { return new Selection(...this.flatMap(element => element.children).filter(element => !selector || matches(element, selector))); }
  first() { return new Selection(...this.slice(0, 1)); }
  filter(callback) { return new Selection(...Array.from(this).filter((element, index) => callback.call(element, index, element))); }
  appendTo(target) { target.append(this); return this; }
  addClass(name) { for (const element of this) element.classes.add(name); return this; }
  toggleClass(name, enabled) { for (const element of this) enabled ? element.classes.add(name) : element.classes.delete(name); return this; }
  on(event, callback) { for (const element of this) element.events[event] = callback; return this; }
  prop(key, value) { for (const element of this) { element.properties ||= {}; element.properties[key] = value; } return this; }
  attr(key, value) { if (value === undefined) return this[0]?.attributes?.[key]; for (const element of this) { element.attributes ||= {}; element.attributes[key] = value; } return this; }
  val(value) { if (value === undefined) return this[0]?.value || ''; return this.text(value); }
  focus() { for (const element of this) element.focused = true; return this; }
  trigger(event) { for (const element of this) element.events[event]?.(); return this; }
  removeClass(name) { for (const element of this) element.classes.delete(name); return this; }
  scrollTop() { return this; }
}
function $(value) {
  if (typeof value !== 'string') return new Selection(value);
  if (value.startsWith('#')) { if (!surfaces[value]) surfaces[value] = node(); return new Selection(surfaces[value]); }
  const tag = value.match(/^<(\w+)/)[1];
  return new Selection(node(tag, value.match(/class="([^"]*)"/)?.[1] || ''));
}

const surfaces = {};
$.extend = Object.assign;
$.trim = value => String(value).trim();

const $messages = $(node());
const turn = $(node('div', 'geo-agent-demo__turn is-assistant'));
turn.data('tools', ['提出代码改动']);
const content = $(node());
turn.append(content);
$messages.append(turn);
const decisions = [];
const notices = [], clearedDrafts = [];
const context = { $, $messages, $home: $(node()), $toolsPage: $(node()), $agentView: $(node()), $account: $('#geo-agent-demo-account'), canSend: true, modelReady: true, showcases: [], showcaseLoadFailed: false, pendingApproval: null, send: decision => decisions.push(decision),
  attachmentFullUrls: Object.create(null), imageExts: ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'],
  sending: false, editingMessage: null, chatRevision: '', $input: $(node()), chatId: 0, wakingLanguageTask: false, currentAgentToken: () => 'fixture-token',
  draftKey: 'fixture-draft', sessionStorage: { removeItem: key => clearedDrafts.push(key) }, layui: { layer: { msg: message => notices.push(message) } },
  syncChatState() {}, loadSessions() {}, loadPatches() {}, loadLanguageTasks() {}, renderMarkdown: text => text };
vm.createContext(context);
vm.runInContext(['renderShowcases', 'showWelcome', 'renderNewChat', 'startNewChat', 'startShowcase', 'openAccount', 'setAgentView', 'setDrawerSessionsExpanded', 'renderLanguageTasks', 'wakeLanguageTasks', 'statusText', 'approvalContentText', 'renderLanguageApproval', 'renderApproval', 'renderPatches', 'patchCard', 'addMessage', 'formatDuration', 'openChat',
  'renderChatMessages', 'startMessageEdit', 'cancelMessageEdit', 'syncEditableMessages', 'copyMessage',
  'splitMessageAttachments', 'syncComposerAttachments', 'createAttachmentThumb', 'addAttachmentThumb', 'appendAttachment', 'isImageUrl'].map(pageFunction).join('\n'), context);
const approval = { run_id: 'approval-run', attempt: 2, actions: [{ name: 'cms_article_create', label: '添加文章', inputs: {
  language: 'cn', scode: '2', title: 'badoucms 介绍', content: '<p>待审批正文 &amp; 内容</p><script>危险脚本</script>', keywords: 'CMS,内容管理',
} }] };
context.renderApproval({ status: $(node('span')), content }, approval);

// 首页和功能页切换只隐藏聊天区，不清空审批与对话；历史展开不改变当前会话。
const heldApproval = context.pendingApproval;
const heldTurn = context.$messages[0].children[0];
for (const view of ['home', 'tools', 'chat']) {
  context.setAgentView(view);
  assert.equal(context.pendingApproval, heldApproval);
  assert.equal(context.$messages[0].children[0], heldTurn);
  assert.equal(context.$messages[0].properties.hidden, view !== 'chat');
  assert.equal(context.$home[0].properties.hidden, view !== 'home');
  assert.equal(context.$toolsPage[0].properties.hidden, view !== 'tools');
}
context.drawerSessionsKey = 'fixture-sessions';
const storedPanels = [];
context.localStorage = { setItem: (...args) => storedPanels.push(args) };
context.setDrawerSessionsExpanded(true, true);
assert.equal(context.$agentView[0].classes.has('is-session-expanded'), true);
assert.equal(surfaces['#geo-agent-demo-history'].attributes['aria-hidden'], 'false');
assert.deepEqual(storedPanels[0], ['fixture-sessions', '1']);
context.setDrawerSessionsExpanded(false, true);
assert.equal(context.$agentView[0].classes.has('is-session-collapsed'), true);
assert.equal(surfaces['#geo-agent-demo-sessions-expand'].attributes['aria-expanded'], 'false');
assert.equal(context.pendingApproval, heldApproval);

assert.equal(content.find('button').length, 2, '审批未生成批准和取消按钮');
context.renderPatches([]);
assert.equal(content.find('.geo-agent-demo__approval').length, 1, '空代码改动列表删除了工具审批卡片');
assert.equal(content.find('button').length, 2, '刷新后审批按钮消失');
assert.equal(content.find('.geo-agent-demo__approval-title')[0].value, 'badoucms 介绍', '文章标题未单独预览');
assert.equal(content.find('.geo-agent-demo__approval-body')[0].value, '待审批正文 & 内容', '正文仍是 HTML 源码或包含脚本内容');
assert.equal(content.find('details').length, 1, '完整参数没有放入折叠区域');
assert.ok(content.find('.geo-agent-demo__approval-raw')[0].value.includes('"scode": "2"'), '完整参数丢失');
for (const status of ['pending', 'applied']) {
  context.renderPatches([{ id: 3, file: 'demo.php', status, diff: '+示例' }]);
  assert.equal($messages.find('.geo-agent-demo__code-patch').length, 1, '重复刷新留下旧代码改动卡片');
  assert.equal(content.find('.geo-agent-demo__approval').length, 1, '非空代码改动列表删除了工具审批卡片');
}
context.renderPatches([]);
assert.equal($messages.find('.geo-agent-demo__code-patch').length, 0, '旧代码改动卡片未清理');
assert.equal(context.pendingApproval, approval, '审批状态在刷新中丢失');
Array.from(content.find('button')).find(button => button.value === '批准并保存草稿').events.click();
Array.from(content.find('button')).find(button => button.value === '取消操作').events.click();
assert.deepEqual(JSON.parse(JSON.stringify(decisions)), [
  { decision: 'approve', run_id: 'approval-run', attempt: 2 },
  { decision: 'reject', run_id: 'approval-run', attempt: 2 },
], '审批按钮未提交原运行和状态版本');

const categoryContent = $(node());
const categoryApproval = { run_id: 'category-edit-run', attempt: 1, actions: [{ name: 'cms_category_update', label: '修改栏目',
  inputs: { language: 'cn', scode: '23', snapshot: 'e'.repeat(64), aucode: 'geo_service_solutions', description: '' },
  display: { title: '修改栏目：服务解决方案', changes: [{ label: '通用编码', before: '', after: 'geo_service_solutions' },
    { label: 'SEO 描述', before: '<img src=x onerror=alert(1)>', after: '' }] },
}] };
context.renderApproval({ status: $(node()), content: categoryContent }, categoryApproval);
assert.ok(categoryContent.find('dd').some(field => field.value === '（空） → geo_service_solutions'));
assert.ok(categoryContent.find('dd').some(field => field.value === '<img src=x onerror=alert(1)> → （清空）'));
assert.equal(categoryContent.find('img').length, 0, '栏目修改对照执行了 HTML');
assert.ok(!categoryContent.find('dt').some(field => field.value === 'snapshot'));
assert.equal(Array.from(categoryContent.find('button')).find(button => button.value === '确认保存栏目').properties.disabled, false);
categoryApproval.actions[0].display = { error: '栏目已变化，请重新读取栏目详情后提交修改' };
context.renderApproval({ status: $(node()), content: categoryContent }, categoryApproval);
assert.ok(categoryContent.find('p').some(field => field.value.includes('栏目已变化')));
assert.equal(Array.from(categoryContent.find('button')).find(button => button.value === '确认保存栏目').properties.disabled, true);

const languageContent = $(node());
const languageApproval = { run_id: 'language-run', attempt: 1, actions: [{ name: 'cms_language_copy', label: '复制语言内容',
  inputs: { snapshot: 's'.repeat(64), source: 'cn', target: 'en', include_content: false, include_settings: false },
  display: { type: 'language_copy', title: '复制栏目', source: '中文', target: '英文', scope: '全部栏目',
    categories: 3, skipped_categories: 1, contents: 0, skipped_contents: 0, settings: 0, include_content: false,
    include_settings: false, category_names: ['新闻', '关于我们'], notes: ['复制成功后再生成译文供确认。'] },
}] };
context.renderApproval({ status: $(node()), content: languageContent }, languageApproval);
assert.equal(languageContent.find('.geo-agent-demo__approval-heading')[0].value, '复制栏目');
assert.deepEqual(Array.from(languageContent.find('.geo-agent-demo__approval-route').children()).filter(child => child.tag === 'span').map(child => child.value), ['中文', '英文']);
assert.ok(languageContent.find('dd').some(field => field.value === '新增 3 个，已有 1 个不重复复制'));
assert.ok(languageContent.find('dd').some(field => field.value === '不复制'));
assert.ok(!languageContent.find('dt').some(field => field.value === 'snapshot' || field.value === 'source'));
assert.ok(languageContent.find('.geo-agent-demo__approval-raw')[0].value.includes('s'.repeat(64)), '校验码应仅保留在完整参数中');
const translationApproval = { run_id: 'translation-run', attempt: 1, actions: [{ name: 'cms_language_translate', label: '保存翻译',
  inputs: { snapshot: 't'.repeat(64), items: [{ id: 12, fields: { name: 'News' } }] },
  display: { type: 'language_translation', title: '翻译栏目', source: '中文', target: '英文',
    items: [{ name: '新闻', fields: [{ label: '栏目名称', before: '新闻', after: 'News' },
      { label: '正文', before: '<p>中文</p>', after: '<p>English</p><script>危险脚本</script>' }] }], notes: ['已有不同译文不覆盖。'] },
}] };
context.renderApproval({ status: $(node()), content: languageContent }, translationApproval);
assert.ok(languageContent.find('dt').some(field => field.value === '栏目名称 · 原文'));
assert.ok(languageContent.find('dt').some(field => field.value === '栏目名称 · 译文'));
assert.ok(languageContent.find('dd').some(field => field.value === 'News'));
assert.ok(languageContent.find('dd').some(field => field.value === 'English'));
assert.equal(languageContent.find('script').length, 0, '译文预览执行了 HTML');
assert.ok(languageContent.find('button').some(button => button.value === '确认保存译文'));
assert.equal(Array.from(languageContent.find('button')).find(button => button.value === '确认保存译文').properties.disabled, false, '有效译文确认按钮置灰');
translationApproval.actions[0].display = { error: '数据已变化，请重新预览' };
context.renderApproval({ status: $(node()), content: languageContent }, translationApproval);
assert.equal(Array.from(languageContent.find('button')).find(button => button.value === '确认保存译文').properties.disabled, true, '失效预览仍允许点击确认');
assert.equal(Array.from(languageContent.find('button')).find(button => button.value === '取消操作').properties.disabled, false);
translationApproval.actions[0].display.can_refresh = true;
context.renderApproval({ status: $(node()), content: languageContent }, translationApproval);
const refreshTranslation = Array.from(languageContent.find('button')).find(button => button.value === '重新读取并生成译文');
assert.equal(refreshTranslation.properties.disabled, false, '重新生成入口仍然置灰');
assert.ok(!languageContent.find('button').some(button => button.value === '确认保存译文'), '失效时仍显示无法保存的按钮');
refreshTranslation.events.click();
assert.deepEqual(JSON.parse(JSON.stringify(decisions.at(-1))), { decision: 'refresh', run_id: 'translation-run', attempt: 1 });

const taskApproval = { run_id: 'task-run', attempt: 1, actions: [{ name: 'cms_language_task_start', label: '提交后台任务',
  inputs: { source: 'cn', target: 'en', kind: 'contents', snapshot: 'q'.repeat(64) },
  display: { type: 'language_task', title: '后台翻译', source: '中文', target: '英文', total: 33, kind: 'contents', scope: '全部栏目',
    copy_first: false, category_names: ['新闻'], notes: ['只需确认这一次，后续自动保存。'] },
}] };
context.renderApproval({ status: $(node()), content: languageContent }, taskApproval);
assert.ok(languageContent.find('dd').some(field => field.value === '后台自动完成，只确认这一次'));
assert.ok(languageContent.find('button').some(button => button.value === '确认并开始后台任务'));
turn.attr('data-run-id', 'queue-run');
function taskFixture(status, progress = {}, error = '') {
  return { id: 7, type: 'cms.translate', run_id: 'queue-run', status, error,
    display: { source: '中文', target: '英文', kind: 'contents', total: 33 },
    progress: { stage: 'translate', total: 33, processed: 10, saved: 10, skipped: 0, ...progress } };
}
context.renderLanguageTasks([taskFixture('running')]);
let taskList = $messages.find('.geo-agent-demo__task-list');
assert.equal(taskList.length, 1);
assert.equal(taskList.find('.geo-agent-demo__task-track')[0].attributes['aria-valuenow'], 30);
assert.ok(taskList.find('.geo-agent-demo__task-detail')[0].value.includes('保存 10'));
let cancelPayload;
context.badou = { api: { ajax(options) { cancelPayload = options.data; } } };
taskList.find('button')[0].events.click();
assert.equal(cancelPayload.action, 'task_cancel');
assert.equal(cancelPayload.task_id, 7);
context.renderLanguageTasks([taskFixture('failed', {}, '模型繁忙')]);
taskList = $messages.find('.geo-agent-demo__task-list');
assert.ok(taskList.find('.geo-agent-demo__task-state')[0].children.some(child => child.value === '执行失败'));
assert.ok(taskList.find('.geo-agent-demo__task-detail')[0].value.includes('模型繁忙'));
assert.equal(taskList.find('button')[0].value, '继续剩余任务');
taskList.find('button')[0].events.click();
assert.equal(cancelPayload.action, 'task_retry');
context.renderLanguageTasks([taskFixture('completed', { processed: 33, saved: 33 })]);
taskList = $messages.find('.geo-agent-demo__task-list');
assert.equal(taskList.find('button').length, 0, '已完成任务仍显示停止按钮');
assert.equal(taskList.find('.geo-agent-demo__task-track')[0].attributes['aria-valuenow'], 100);
context.renderLanguageTasks([taskFixture('completed', { processed: 33, saved: 31, skipped: 2,
  skipped_items: [{ source_id: 87, target_id: 151, name: '<img src=x onerror=alert(1)>',
    reason: 'unchanged', reason_label: '模型保持原样，未生成新译文' }], skipped_items_truncated: true })]);
taskList = $messages.find('.geo-agent-demo__task-list');
assert.equal(taskList.find('summary')[0].value, '查看跳过记录（1 条）');
assert.ok(taskList.find('.geo-agent-demo__task-detail').some(item => item.value.includes('源 ID 87 / 目标 ID 151')
  && item.value.includes('未生成新译文')), '没有展示跳过编号及通俗原因');
assert.equal(taskList.find('img').length, 0, '跳过记录标题被当作 HTML 执行');
assert.ok(taskList.find('.geo-agent-demo__task-detail').some(item => item.value.includes('100 条展示上限')), '未说明跳过明细截断');
context.renderLanguageTasks([]);
assert.equal($messages.find('.geo-agent-demo__task-list').length, 0);

// FPM 接续使用独立 POST 和 CSRF，重复轮询不并发唤醒，运行或结束的任务不触发。
let wakeOptions, wakeDone;
context.badou = { api: { ajax(options, callback) { wakeOptions = options; wakeDone = callback; } } };
context.wakeLanguageTasks([taskFixture('running')]);
assert.equal(wakeOptions, undefined);
context.wakeLanguageTasks([taskFixture('queued')]);
assert.equal(wakeOptions.type, 'POST');
assert.equal(wakeOptions.data.action, 'task_wake');
assert.equal(wakeOptions.data.__token__, 'fixture-token');
context.wakeLanguageTasks([taskFixture('interrupted')]);
assert.equal(context.wakingLanguageTask, true);
wakeDone();
assert.equal(context.wakingLanguageTask, false);
context.wakeLanguageTasks([taskFixture('completed')]);
assert.equal(context.wakingLanguageTask, false);

// 重新打开会话时，把审批放回相同 run_id 的回答，不再追加重复提示。
const attachedQuestion = '写一篇文章\n已上传文件，附件ID：9：/uploads/说明.docx';
context.badou = { api: { ajax(options, callback) {
  assert.equal(options.url, 'agent/history');
  callback({ revision: 'f'.repeat(64), messages: [
    { role: 'user', message_id: 'a'.repeat(32), content: attachedQuestion },
    { role: 'assistant', run_id: approval.run_id, content: '请确认以下操作，批准后继续执行。', tools: ['查询栏目'], duration_ms: 23000 },
  ], approval });
} } };
context.loadPatches = () => context.renderPatches([]);
context.openChat(7);
assert.equal($messages.find('.geo-agent-demo__turn.is-assistant').length, 1, '会话恢复新增了重复的审批回答');
assert.equal($messages.find('.geo-agent-demo__approval').length, 1, '会话恢复没有保留审批卡片');
assert.equal($messages.find('.geo-agent-demo__status')[0].value, '等待确认', '待审批回答被显示为已完成');
assert.equal($messages.find('.geo-agent-demo__tools').length, 1, '原回答的工具记录丢失');
const firstUser = $messages.find('.geo-agent-demo__turn.is-user');
assert.equal(firstUser.find('.geo-agent-demo__message-attachments').length, 1, '历史消息附件卡片没有恢复');
const sentCard = firstUser.find('.geo-agent-demo__attachment');
assert.equal(sentCard[0].attributes.href, '/uploads/说明.docx', '已发送附件未关联文件地址');
assert.equal(sentCard.find('.btn-remove').length, 0, '已发送附件不应显示移除按钮');
assert.equal(firstUser.find('.geo-agent-demo__bubble').find('div')[0].value, '写一篇文章', '附件地址标记未转换为卡片');
assert.equal(firstUser.find('.geo-agent-demo__message-action').length, 2, '用户消息下面没有复制和编辑按钮');
context.$input.val('未发送的草稿');
context.startMessageEdit(firstUser);
assert.equal(context.$input.val(), attachedQuestion);
assert.equal($('#geo-agent-demo-attachments').children().length, 1, '编辑带附件消息时没有恢复待发送预览');
assert.equal(context.editingMessage.id, 'a'.repeat(32));
assert.equal(context.editingMessage.revision, 'f'.repeat(64));
assert.equal(context.editingMessage.chatId, 7);
assert.equal(context.pendingApproval, approval, '进入编辑模式不应执行或取消原会话审批');
const laterUser = context.addMessage({ role: 'user', content: '较晚的问题', message_id: 'b'.repeat(32) });
context.startMessageEdit(laterUser.turn);
assert.equal(context.editingMessage.draft, '未发送的草稿', '改选其他历史消息丢失原输入草稿');
context.cancelMessageEdit(true);
assert.equal(context.$input.val(), '未发送的草稿');
assert.equal($('#geo-agent-demo-attachments').children().length, 0, '取消编辑后遗留了上一条消息附件');
assert.equal(context.editingMessage, null);
assert.equal($messages.find('.is-editing').length, 0);
assert.equal(surfaces['#geo-agent-demo-edit-banner'].properties.hidden, true);

// 截图中的旧消息把提问拼在地址末尾；卡片和正文必须是两个独立兄弟节点。
const gluedQuestion = '已上传文件，附件ID：342：/uploads/20261008/abcdef.doc解析一下这个文档。';
const repaired = context.addMessage({ role: 'user', content: gluedQuestion, message_id: 'd'.repeat(32) }).turn;
const repairedWrap = repaired.find('.geo-agent-demo__user-wrap');
assert.equal(repairedWrap.children()[0].classes.has('geo-agent-demo__message-attachments'), true);
assert.equal(repairedWrap.children()[1].classes.has('geo-agent-demo__bubble'), true);
assert.equal(repaired.find('.geo-agent-demo__attachment').attr('href'), '/uploads/20261008/abcdef.doc');
assert.equal(repaired.find('.geo-agent-demo__file').find('span')[0].value, 'DOC');
assert.equal(repaired.find('.geo-agent-demo__bubble').find('div')[0].value, '解析一下这个文档。');
assert.equal(repaired.data('messageText'), gluedQuestion, '展示修复不应改写历史原文');
context.$input.val(gluedQuestion);
context.syncComposerAttachments();
$('#geo-agent-demo-attachments').find('.btn-remove')[0].events.click();
assert.equal(context.$input.val(), '解析一下这个文档。', '移除附件误删提问');
context.$input.val('');
context.appendAttachment('/uploads/new.docx', 'https://example.test/uploads/new.docx', 343);
assert.equal(context.$input.val().endsWith('/uploads/new.docx\n'), true, '上传后没有换行');
assert.equal(context.$input[0].selectionStart, context.$input.val().length, '光标没有移到新行');
context.$input.val(context.$input.val() + '解析一下这个文档。');
const newMessage = context.splitMessageAttachments(context.$input.val());
assert.equal(newMessage.text, '解析一下这个文档。');
assert.deepEqual(Array.from(newMessage.attachments), ['/uploads/new.docx']);
context.$input.val('');
context.syncComposerAttachments();

context.sending = true;
context.startMessageEdit(firstUser);
assert.equal(context.editingMessage, null, '生成过程中仍能切换编辑目标');
context.sending = false;
const optimistic = context.addMessage({ role: 'user', content: '新问题' });
context.syncEditableMessages({ revision: 'c'.repeat(64), run_id: 'new-run', messages: [
  { role: 'user', message_id: 'c'.repeat(32), content: '新问题' },
  { role: 'assistant', run_id: 'new-run', content: '新回答' },
] }, optimistic.turn);
assert.equal(optimistic.turn.data('messageId'), 'c'.repeat(32), '新消息完成后没有变为可编辑');
assert.equal(firstUser.data('messageId'), '', '已截出存储范围的旧消息仍可以编辑');

(async () => {
  const copied = [];
  context.navigator = { clipboard: { async writeText(value) { copied.push(value); } } };
  context.setTimeout = () => {};
  const button = $(node('button'));
  await context.copyMessage('原文\n<img src=x>', button);
  assert.deepEqual(copied, ['原文\n<img src=x>'], '复制改变了用户原始文字');
  assert.equal(button[0].attributes.title, '已复制');
  let fallbackValue, removed = false;
  context.navigator = {};
  context.document = {
    createElement() { return { style: {}, select() { fallbackValue = this.value; }, remove() { removed = true; } }; },
    body: { appendChild() {} }, execCommand(command) { assert.equal(command, 'copy'); return true; },
  };
  await context.copyMessage('兼容复制', button);
  assert.equal(fallbackValue, '兼容复制');
  assert.equal(removed, true);
  console.log('审批卡片/会话恢复、语言复制范围摘要、译文对照/纯文本展示、整项任务审批/进度/停止/续跑，用户消息复制及历史编辑检查通过');
})().catch(error => { console.error(error); process.exitCode = 1; });


// 新对话切换到空白聊天区，清除编辑、附件和草稿；历史消息仍由服务端保留。
context.editingMessage = { draft: attachedQuestion };
context.$input.val(attachedQuestion);
context.chatId = 7;
context.chatRevision = '旧版本';
context.pendingApproval = approval;
context.setDrawerSessionsExpanded(true, true);
assert.equal(context.startNewChat(), true);
assert.equal(context.$agentView[0].classes.has('is-session-collapsed'), true);
assert.equal(surfaces['#geo-agent-demo-sessions-expand'].attributes['aria-expanded'], 'false');
assert.equal(surfaces['#geo-agent-demo-history'].attributes['aria-hidden'], 'true');
assert.equal(context.chatId, 0);
assert.equal(context.startedNewChat, true);
assert.equal(context.editingMessage, null);
assert.equal(context.pendingApproval, null);
assert.equal(context.chatRevision, '');
assert.equal(context.$input.val(), '');
assert.equal(context.$input[0].focused, true);
assert.equal($('#geo-agent-demo-attachments').children().length, 0);
assert.equal(context.$home[0].properties.hidden, true);
assert.equal(context.$messages[0].properties.hidden, false);
assert.equal(context.$messages.find('.geo-agent-demo__turn').length, 0);
assert.equal(context.$messages.find('.geo-agent-demo__empty-chat').length, 1);
assert.ok(clearedDrafts.includes('fixture-draft'));

// 历史请求晚于新建操作返回时，不得把旧会话重新显示出来。
let historyCallback;
context.badou = { api: { ajax(options, success) {
  assert.equal(options.url, 'agent/history');
  historyCallback = success;
} } };
context.openChat(7);
context.setDrawerSessionsExpanded(true, true);
context.startNewChat();
const newChatContent = context.$messages[0].children[0];
assert.equal(historyCallback({ messages: [{ role: 'assistant', content: '旧会话内容' }] }), false);
assert.equal(context.chatId, 0);
assert.equal(context.$messages[0].children[0], newChatContent);
assert.equal(context.$agentView[0].classes.has('is-session-collapsed'), true);

// 账号弹窗复用插件管理的会员模板，且不改变历史栏、对话和草稿。
context.setDrawerSessionsExpanded(false, false);
const accountRequests = [], dialogs = [], balanceCallbacks = [];
const officialUser = { id: 8, nickname: '<img src=x> 用户', token: 'private-account-token' };
context.accountDialogIndex = null;
context.document = { body: node('body') };
context.window = { innerWidth: 1200, innerHeight: 900 };
context.Config = { api_url: 'https://account.example', bdversion: 'fixture-version' };
context.badou = { api: {
  badouStorage: { get: () => officialUser, getSid: () => 'fixture-sid' },
  ajax(options, success, error) { accountRequests.push({ options, success, error }); }
} };
context.laytpl = template => ({ render(user) { assert.equal(user, officialUser); return '官网资料：' + template; } });
context.fetchAiBalance = callback => balanceCallbacks.push(callback);
context.layui.layer.open = options => { dialogs.push(options); return dialogs.length; };
context.layui.layer.close = index => dialogs[index - 1].end();
context.$input.val('账号弹窗打开前的草稿');
const accountChat = context.chatId;
context.pendingApproval = approval;
context.openAccount();
assert.equal(context.$agentView[0].classes.has('is-session-collapsed'), true);
assert.equal(context.chatId, accountChat);
assert.equal(context.pendingApproval, approval);
assert.equal(context.$input.val(), '账号弹窗打开前的草稿');
assert.equal(dialogs[0].title, '账号信息');
assert.equal(dialogs[0].type, 1);
assert.deepEqual(Array.from(dialogs[0].area), ['540px', '560px']);
const accountPanel = dialogs[0].content;
assert.equal(accountPanel.find('.geo-agent-profile__name')[0].value, officialUser.nickname);
assert.equal(accountPanel.find('img').length, 0, '昵称中的 HTML 被执行');
assert.equal(accountRequests[0].options.url, 'https://account.example/api/member/userinfotpl');
assert.equal(accountRequests[0].options.data.token, officialUser.token);
assert.equal(accountRequests[0].success('会员资料模板'), false);
assert.equal(accountPanel.find('.geo-agent-profile__details')[0].value, '官网资料：会员资料模板');
balanceCallbacks[0](null, 120);
assert.equal(accountPanel.find('.geo-agent-profile__points')[0].value, '120 积分');
context.openAccount();
assert.equal(dialogs.length, 1, '重复点击打开多个账号弹窗');
let packagesOpened = 0;
context.openAiPackages = () => packagesOpened++;
dialogs[0].yes(1);
assert.equal(packagesOpened, 1);
assert.equal(context.accountDialogIndex, null);
assert.equal(context.document.body.children.length, 0, '关闭弹窗后遗留临时账号面板');
assert.equal(surfaces['#geo-agent-demo-account-open'].focused, true);
context.window = { innerWidth: 375, innerHeight: 640 };
context.openAccount();
assert.deepEqual(Array.from(dialogs[1].area), ['345px', '560px']);
assert.equal(accountRequests[1].error({}, { code: 0, msg: '网络暂不可用' }), false);
assert.equal(dialogs[1].content.find('.geo-agent-profile__status')[0].value, '网络暂不可用');
balanceCallbacks[1](new Error('余额失败'));
assert.equal(dialogs[1].content.find('.geo-agent-profile__points')[0].value, '暂时无法查询');
// 旧请求迟到时不能覆盖已经关闭或重新打开的账号弹窗。
accountRequests[0].success('迟到的模板');
balanceCallbacks[0](null, 999);
assert.equal(accountPanel.find('.geo-agent-profile__points')[0].value, '120 积分');
dialogs[1].end();
let loginRequested = null;
context.openAgentLogin = afterLogin => { loginRequested = afterLogin; };
context.badou.api.badouStorage.get = () => null;
context.openAccount();
assert.equal(loginRequested, false);
assert.equal(dialogs.length, 2);
assert.equal(context.$agentView[0].classes.has('is-session-collapsed'), true);
assert.match(html, /id="geo-agent-demo-account-open" aria-haspopup="dialog"/);

// 首页采用六项概览，功能中心保留全部示例；点击示例新建会话并发送。
context.modelReady = true;
context.showcaseLoadFailed = false;
context.showcases = Array.from({ length: 7 }, (_, index) => ({ title: '功能' + index, icon: 'fa-cube',
  description: '功能说明', prompts: ['<img src=x onerror=alert(1)> 示例' + index, '补充示例' + index] }));
context.$messages.empty();
context.setAgentView('chat');
assert.equal(context.$messages.find('.geo-agent-demo__start-option').length, 3);
assert.equal(context.$messages.find('h2')[0].value, '今天，先处理哪件事？');
assert.equal(context.$messages.find('img').length, 0);
const newPrompt = context.$messages.find('.geo-agent-demo__start-option')[0];
const beforeNewPrompt = decisions.length;
newPrompt.events.click();
assert.equal(decisions.length, beforeNewPrompt + 1, '新对话快捷任务未自动发送');
assert.equal(context.$input.val(), '<img src=x onerror=alert(1)> 示例0');
context.renderShowcases(context.$home, false);
context.renderShowcases(context.$toolsPage, true);
assert.equal(context.$home.find('.geo-agent-demo__card').length, 6);
assert.equal(context.$home.find('.geo-agent-demo__example').length, 6);
assert.equal(context.$toolsPage.find('.geo-agent-demo__card').length, 7);
assert.equal(context.$toolsPage.find('.geo-agent-demo__example').length, 14);
const decisionsBeforePrompt = decisions.length;
const prompt = context.$home.find('.geo-agent-demo__example')[0];
context.chatId = 7;
context.$input.val(attachedQuestion);
prompt.events.click();
assert.equal(context.$input.val(), '<img src=x onerror=alert(1)> 示例0');
assert.equal(context.chatId, 0);
assert.equal($('#geo-agent-demo-attachments').children().length, 0);
assert.equal(decisions.length, decisionsBeforePrompt + 1);
assert.equal(prompt.children[0].value, '<img src=x onerror=alert(1)> 示例0');
context.sending = true;
context.chatId = 9;
context.$input.val('生成中的草稿');
prompt.events.click();
assert.equal(context.chatId, 9);
assert.equal(context.$input.val(), '生成中的草稿');
assert.equal(decisions.length, decisionsBeforePrompt + 1);
assert.ok(notices.some(message => message.includes('请先停止')));
context.sending = false;
context.canSend = false;
context.$messages.empty();
context.setAgentView('chat');
assert.ok(context.$messages.find('.geo-agent-demo__start-option').every(element => element.properties.disabled));
assert.ok(context.$messages.find('.geo-agent-demo__start-tip')[0].value.includes('没有发送消息的权限'));
context.renderShowcases(context.$home, false);
assert.ok(context.$home.find('.geo-agent-demo__example').every(element => element.properties.disabled));
prompt.events.click();
assert.equal(decisions.length, decisionsBeforePrompt + 1, '权限失效后仍能调用 AI');
context.canSend = true;
context.modelReady = false;
context.$messages.empty();
context.setAgentView('chat');
assert.ok(context.$messages.find('.geo-agent-demo__start-option').every(element => element.properties.disabled));
context.renderShowcases(context.$home, false);
assert.ok(context.$home.find('.geo-agent-demo__example').every(element => element.properties.disabled));
prompt.events.click();
assert.equal(decisions.length, decisionsBeforePrompt + 1, '未登录时仍能调用 AI');
context.showcases = [];
context.renderShowcases(context.$home, false);
assert.equal(context.$home.find('.geo-agent-demo__example').length, 0);
context.canSend = true;
console.log('新对话任务入口/清理草稿与附件、独立账号弹窗/官网模板/积分/失败状态/防重复、功能自动发送与权限禁用检查通过');

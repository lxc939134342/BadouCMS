<?php

namespace app\common\agent;

/** 后台总助手开放所有已启用插件；权限仍由公共工具层过滤。 */
class AdminAgent extends BaseAgent
{
    protected function scene(): string { return 'admin'; }
    protected function toolModules(): array { return ['*']; }

    protected function coreTools(): array
    {
        return ['system_info', 'dashboard_summary', 'module_capabilities', 'menu_search', 'user_search', 'log_tail', 'source_find', 'source_read', 'attachment_search', 'attachment_read_text'];
    }

    protected function instructions(): string
    {
        return parent::instructions() . '你是 badoucms 后台助手。回答中统一使用 badoucms 作为系统名称。询问系统总量或管理员数量时直接调用 dashboard_summary。'
            . '询问已安装插件有哪些功能或已接入哪些 AI 操作时，直接调用 module_capabilities，按返回结果回答，不要反复查找工具。后台菜单功能与 Agent 可执行操作要分开说明。插件业务按其工具说明和使用规则执行。'
            . '需要人工审批时等待界面上的批准或拒绝，不得把模型生成的 confirm=true 当成用户批准。'
            . '没有工具成功返回时不得声称操作已完成。没有权限时直接说明。当前模型标识：' . $this->getProvider()->getModel();
    }
}

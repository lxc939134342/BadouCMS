<?php

namespace app\common\agent\tools;

use app\common\agent\AgentContext;
use app\common\agent\AbstractAgentTool;
use app\common\agent\services\DashboardAgentService;

class DashboardSummaryTool extends AbstractAgentTool
{
    protected string $label = '查看控制台汇总';

    public function name(): string
    {
        return 'dashboard_summary';
    }

    public function description(): string
    {
        return '读取控制台汇总：会员数、管理员数、已安装插件数、数据表数量、数据库大小、附件数量和图片数量。';
    }

    public function permission(): string
    {
        return 'dashboard/index';
    }

    public function properties(): array
    {
        return [];
    }

    public function execute(AgentContext $context, array $input): array
    {
        return (new DashboardAgentService())->summary();
    }
}

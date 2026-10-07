<?php

namespace app\common\agent\tools;

use app\common\agent\{AbstractAgentToolProvider, AgentContext};
use app\common\agent\services\CrudAgentService;

class CrudToolProvider extends AbstractAgentToolProvider
{
    public function guidelines(): string
    {
        return '用户要求设计数据表或创建后台管理功能时使用 crud_design_info 查看类型与命名规则，根据业务需求设计 fields（包含自增 id、必要业务字段和时间字段），调用 crud_preview 展示方案，再以完全相同的参数和 snapshot 调用 crud_generate 等待批准。直接调用项目 CRUD 生成器，不通过代码编辑工具手写 CRUD。不覆盖已有表或文件，不猜测业务必需字段，需求不清楚时先询问。只有生成工具成功才报告已创建。';
    }

    public function agentTools(): array
    {
        $properties = [
            ['name' => 'table', 'type' => 'string', 'description' => '新表名，不带数据库前缀，snake_case', 'required' => true],
            ['name' => 'title', 'type' => 'string', 'description' => '中文功能及菜单名称', 'required' => true],
            ['name' => 'controller', 'type' => 'string', 'description' => '小写点分路径，例如 sales.customer；省略按表名生成'],
            ['name' => 'fields', 'type' => 'array', 'description' => '字段对象数组。每项 name/type/comment，可选 length/required/signed/default/primary/index/precision/scale/values。必须含 id:int 或 bigint 且 primary=true；字段属性按 crud_design_info 返回规则设计。', 'required' => true],
        ];
        return [
            ['name' => 'crud_design_info', 'label' => '查询一键 CRUD 设计规则', 'description' => '读取现有表名、支持的字段类型及原 CRUD 组件识别规则，不修改数据。', 'permission' => 'tabledesign/index', 'properties' => [], 'handler' => static fn (AgentContext $c, array $i): array => (new CrudAgentService())->describe($c)],
            ['name' => 'crud_preview', 'label' => '预览后台功能设计', 'description' => '校验新表字段和后台功能命名，返回字段方案、生成文件清单和 snapshot，不建表不写代码。', 'permission' => 'tabledesign/crud', 'properties' => $properties, 'handler' => static fn (AgentContext $c, array $i): array => (new CrudAgentService())->preview($c, $i)],
            ['name' => 'crud_generate', 'label' => '创建数据表及后台 CRUD 功能', 'description' => '审批后复用表设计器和一键 CRUD 生成数据表、控制器、模型、验证器、表格/表单页面、语言文件及权限菜单。只新建，不覆盖已有文件或表。', 'permission' => 'tabledesign/crud', 'approval' => true, 'properties' => array_merge($properties, [['name' => 'snapshot', 'type' => 'string', 'description' => '预览返回的 snapshot', 'required' => true]]), 'approval_display' => static fn (AgentContext $c, array $i): array => (new CrudAgentService())->preview($c, $i), 'handler' => static fn (AgentContext $c, array $i): array => (new CrudAgentService())->generate($c, $i)],
        ];
    }
}

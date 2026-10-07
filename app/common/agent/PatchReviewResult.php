<?php

namespace app\common\agent;

use NeuronAI\StructuredOutput\SchemaProperty;

/** 代码改动自审结果，配合 Agent::structured() 使用。 */
class PatchReviewResult
{
    #[SchemaProperty(description: '是否批准自动应用该改动', required: true)]
    public bool $approve = false;

    #[SchemaProperty(description: '简短的中文理由', required: true)]
    public string $reason = '';
}

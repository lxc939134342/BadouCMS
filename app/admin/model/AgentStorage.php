<?php

namespace app\admin\model;

use NeuronAI\Chat\History\SQLMessageStore;
use NeuronAI\Workflow\Persistence\DatabasePersistence;
use think\facade\Db;
use think\Model;

/** 使用 Neuron 自带的数据库存储，避免自行实现批准状态机。 */
class AgentStorage extends Model
{
    protected $name = 'agent_workflow';

    public static function workflow(): DatabasePersistence
    {
        return new DatabasePersistence(Db::connect()->getPdo(), self::tableName('agent_workflow'));
    }

    public static function messages(): SQLMessageStore
    {
        return new SQLMessageStore(Db::connect()->getPdo(), self::tableName('agent_message'));
    }

    public static function clearThread(string $threadId): void
    {
        // 会话删除只清理已核对身份的线程，不能接收浏览器提供的工作流地址。
        Db::table(self::tableName('agent_workflow'))->where('partition', bin2hex($threadId))->delete();
        self::messages()->clear($threadId);
    }

    private static function tableName(string $name): string
    {
        $prefix = (string)config('database.connections.mysql.prefix', 'bd_');
        if (!preg_match('/^[A-Za-z0-9_]*$/D', $prefix)) throw new \DomainException('Agent 数据库表前缀无效');
        return $prefix . $name;
    }
}

<?php

namespace badou;

/**
 * 事件上下文对象 - 用于实现引用传值和操作拦截
 */
class EventContext
{
    /**
     * 事件数据
     * @var array
     */
    public array $data = [];

    /**
     * 额外参数（如原始行数据 $row）
     * @var array
     */
    public array $params = [];

    /**
     * 是否拦截操作
     * @var bool
     */
    protected bool $isIntercepted = false;

    /**
     * 拦截原因/消息
     * @var string
     */
    protected string $message = '';

    public function __construct(array $data = [], array $params = [])
    {
        $this->data = $data;
        $this->params = $params;
    }

    /**
     * 修改数据
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 获取数据
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * 拦截当前操作
     * @param string $message 拦截原因
     * @return void
     */
    public function intercept(string $message = '操作被拦截'): void
    {
        $this->isIntercepted = true;
        $this->message = $message;
    }

    /**
     * 是否被拦截
     * @return bool
     */
    public function isIntercepted(): bool
    {
        return $this->isIntercepted;
    }

    /**
     * 获取拦截消息
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}

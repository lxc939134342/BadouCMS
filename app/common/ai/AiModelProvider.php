<?php

namespace app\common\ai;

use Generator;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\Stream\Chunks\TextChunk;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Exceptions\HttpException;
use NeuronAI\Exceptions\ProviderException;
use NeuronAI\Providers\OpenAILike;
use NeuronAI\Providers\ProviderResponse;
use NeuronAI\Tools\ToolInterface;
use think\facade\Log;

class AiModelProvider extends OpenAILike
{
    private $retryCallback = null;
    private $checkRunning = null;
    private array $retryDetails = [];

    public function onRetry(?callable $callback): self
    {
        $this->retryCallback = $callback;
        return $this;
    }

    public function onCheck(?callable $check): self
    {
        $this->checkRunning = $check;
        return $this;
    }

    private function check(): void
    {
        if ($this->checkRunning !== null) ($this->checkRunning)();
    }

    public function chat(Message ...$messages): ProviderResponse
    {
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->check();
            try {
                $response = parent::chat(...$messages);
                $this->check();
                $failure = $this->responseFailure($response);
                if ($failure === null) return $response;
                $reason = $failure->getMessage();
            } catch (HttpException | ProviderException $e) {
                $this->check();
                $failure = $e;
                $reason = $this->retryReason($e);
                if ($reason === null) throw $e;
            }
            if ($attempt === 1) throw $failure;
            $this->retryDetails = ['mode' => 'chat', 'fallback' => 'chat',
                'http_status' => $failure instanceof HttpException ? $failure->response?->statusCode : null];
            $this->prepareRetry($reason);
        }
        throw $failure;
    }

    public function stream(Message ...$messages): Generator
    {
        $buffered = false;
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $emitted = false;
            $this->check();
            try {
                if ($buffered) {
                    // 同一份工具结果改用普通响应读取，不重新运行 Agent 或执行工具。
                    $response = parent::chat(...$messages);
                    $this->check();
                    $text = (string)$response->message()->getContent();
                    if ($text !== '') {
                        $emitted = true;
                        yield new TextChunk($response->message()->getId(), $text);
                    }
                } else {
                    $stream = parent::stream(...$messages);
                    foreach ($stream as $chunk) {
                        $this->check();
                        // 参数增量不展示给用户，只有实际文字输出才阻止重试。
                        if ($chunk instanceof TextChunk && $chunk->content !== '') $emitted = true;
                        yield $chunk;
                    }
                    $response = $stream->getReturn();
                }
                $this->check();
                $failure = $this->responseFailure($response);
                if ($failure === null) return $response;
                $reason = $failure->getMessage() . '，改用普通响应读取';
                $buffered = true;
            } catch (HttpException | ProviderException $e) {
                $this->check();
                $failure = $e;
                $reason = $this->retryReason($e);
                // 已向客户端输出的内容不可重复；不重试权限、额度、参数等错误。
                if ($emitted || $reason === null) throw $e;
                $buffered = $e instanceof ProviderException;
            }
            if ($attempt === 1 || $emitted) throw $failure;
            $this->retryDetails = ['mode' => 'stream', 'fallback' => $buffered ? 'chat' : 'stream',
                'http_status' => $failure instanceof HttpException ? $failure->response?->statusCode : null];
            $this->prepareRetry($reason);
        }
        throw $failure;
    }

    private function responseFailure(ProviderResponse $response): ?ProviderException
    {
        $message = $response->message();
        if ($message instanceof ToolCallMessage && $message->getToolCalls() !== []) {
            // 只有工具名、没有必填参数的响应不能交给 Agent 执行；未输出时允许一次普通响应恢复。
            foreach ($message->getToolCalls() as $call) {
                foreach ($this->tools as $tool) {
                    if (!$tool instanceof ToolInterface || $tool->getName() !== $call->getName()) continue;
                    foreach ($tool->getProperties() as $property) {
                        if ($property->isRequired() && !array_key_exists($property->getName(), $call->getInputs())) {
                            Log::warning('AI 工具必填参数缺失：工具={tool}，字段={field}，已返回字段={fields}', [
                                'tool' => $call->getName(), 'field' => $property->getName(),
                                'fields' => implode(',', array_keys($call->getInputs())),
                            ]);
                            return new ProviderException('模型未完整返回工具参数');
                        }
                    }
                }
            }
            return null;
        }
        return trim((string)$message->getContent()) === '' ? new ProviderException('模型返回空响应') : null;
    }

    private function retryReason(\Throwable $e): ?string
    {
        // 网关资源保护需要处理服务器负载，不能当作模型繁忙盲目重试。
        if (preg_match('/system_(?:disk|memory|cpu)_overloaded|system (?:disk|memory|cpu) overloaded/i', $e->getMessage())) return null;
        if ($e instanceof HttpException) {
            return match ($e->response?->statusCode) {
                429 => '模型请求触发限流',
                502 => '模型网关响应异常',
                503 => '模型服务暂不可用',
                504 => '模型网关响应超时',
                default => null,
            };
        }
        if (preg_match('/model.{0,40}busy|busy.{0,40}model|overloaded|temporarily unavailable|模型.{0,20}繁忙/iu', $e->getMessage())) return '模型服务暂时繁忙';
        if ($e->getMessage() === 'The stream ended before the answer was complete.') return '模型流式响应提前中断';
        return null;
    }

    protected function prepareRetry(string $reason): void
    {
        $this->check();
        Log::warning('AI 模型请求重试一次：模型={model}，原因={reason}，方式={mode}→{fallback}，HTTP={http_status}',
            ['model' => $this->getModel(), 'reason' => $reason] + $this->retryDetails);
        if ($this->retryCallback !== null) ($this->retryCallback)($reason);
        if (in_array($reason, ['模型服务暂时繁忙', '模型请求触发限流', '模型服务暂不可用'], true)) sleep(2);
    }
}

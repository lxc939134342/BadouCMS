<?php

namespace app\common\ai;

use NeuronAI\HttpClient\Curl\CurlHttpClient;

/** 传输期间也检查停止状态，等待首字和非流式回退时同样可以中断。 */
class CancellableHttpClient extends CurlHttpClient
{
    public function __construct(callable $checkRunning, float $timeout = 120)
    {
        parent::__construct(timeout: $timeout, connectTimeout: 10, curlOptions: [
            CURLOPT_NOPROGRESS => false,
            CURLOPT_XFERINFOFUNCTION => static function () use ($checkRunning): int {
                try {
                    $checkRunning();
                    return 0;
                } catch (\Throwable $e) {
                    // 返回非零值让 cURL 关闭连接，原始异常由 Provider 的检查恢复。
                    return 1;
                }
            },
        ]);
    }
}

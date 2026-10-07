<?php

namespace app\common\ai;

use GuzzleHttp\Client;
use NeuronAI\HttpClient\Guzzle\GuzzleHttpClient;
use NeuronAI\Providers\AIProviderInterface;
use think\facade\Cache;

/**
 * Agent 的模型出口。官网只签发会员对应的 NewAPI 密钥，模型请求直连 NewAPI。
 */
class AiGateway
{
    private const CREDENTIAL_PATH = 'api/ai/credential';

    public static function rechargeUrl(): string
    {
        return rtrim((string)config('badouadmin.api_url'), '/') . '/ai/recharge';
    }

    /** @return array{uid:int, token:string} */
    public static function account(): array
    {
        $user = Cache::get('bd_u') ?: [];
        $uid = (int)($user['uid'] ?? 0);
        $token = (string)($user['token'] ?? '');
        if ($uid <= 0 || $token === '') {
            throw new AiException('请先在插件管理中登录badoucms官网账号', AiException::NOT_LOGIN, self::rechargeUrl());
        }
        return ['uid' => $uid, 'token' => $token];
    }

    public static function ready(): bool
    {
        try {
            self::account();
            return true;
        } catch (AiException) {
            return false;
        }
    }

    /** @return array{remain_quota:int,used_quota:int,remain_points:float,used_points:float} */
    public static function balance(): array
    {
        $account = self::account();
        try {
            $response = (new Client(['timeout' => 10, 'connect_timeout' => 5]))->get(
                rtrim((string)config('badouadmin.api_url'), '/') . '/api/ai/balance',
                ['headers' => ['token' => $account['token'], 'Accept' => 'application/json']]
            );
            $body = json_decode((string)$response->getBody(), true);
        } catch (\Throwable $e) {
            throw new AiException('暂时无法查询 AI 额度', AiException::UPSTREAM, self::rechargeUrl(), $e);
        }
        if (!is_array($body) || ($body['code'] ?? 0) !== 1 || !is_array($body['data'] ?? null)) {
            throw new AiException((string)($body['msg'] ?? '暂时无法查询 AI 额度'), AiException::UPSTREAM, self::rechargeUrl());
        }
        if (!is_numeric($body['data']['remain_points'] ?? null) || !is_numeric($body['data']['used_points'] ?? null)) {
            throw new AiException('官网尚未提供 AI 积分余额，请更新官网充值服务', AiException::UPSTREAM, self::rechargeUrl());
        }
        return [
            'remain_quota' => max(0, (int)($body['data']['remain_quota'] ?? 0)),
            'used_quota' => max(0, (int)($body['data']['used_quota'] ?? 0)),
            'remain_points' => max(0, (float)$body['data']['remain_points']),
            'used_points' => max(0, (float)$body['data']['used_points']),
        ];
    }

    /** @return array<int, array{id:string,name:string,price:string,points:string,paytypes:array}> */
    public static function packages(): array
    {
        $body = self::rechargeRequest('GET', 'api/ai/packages');
        $packages = [];
        foreach ((array)($body['data'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (!is_numeric($item['points'] ?? null) || (float)$item['points'] <= 0) {
                throw new AiException('官网尚未提供 AI 积分套餐，请更新官网充值服务', AiException::UPSTREAM, self::rechargeUrl());
            }
            $packages[] = [
                'id' => (string)($item['id'] ?? ''),
                'name' => (string)($item['name'] ?? ''),
                'price' => (string)($item['price'] ?? ''),
                'points' => (string)$item['points'],
                'paytypes' => array_values(array_intersect((array)($item['paytypes'] ?? ['alipay']), ['alipay', 'wechat'])),
            ];
        }
        return $packages;
    }

    /** @return array{order_no:string,payurl:string} */
    public static function createOrder(string $packageId, string $paytype): array
    {
        $body = self::rechargeRequest('POST', 'api/ai/order', [
            'package_id' => $packageId,
            'paytype' => $paytype,
        ]);
        $data = (array)($body['data'] ?? []);
        $payurl = (string)($data['payurl'] ?? '');
        $orderNo = (string)($data['order_no'] ?? '');
        $expected = parse_url(rtrim((string)config('badouadmin.api_url'), '/'));
        $actual = parse_url($payurl);
        if (!is_array($actual) || !is_array($expected)
            || strtolower((string)($actual['scheme'] ?? '')) !== strtolower((string)($expected['scheme'] ?? ''))
            || strtolower((string)($actual['host'] ?? '')) !== strtolower((string)($expected['host'] ?? ''))
            || (int)($actual['port'] ?? 0) !== (int)($expected['port'] ?? 0)
            || !preg_match('/^AI[0-9]{14}[a-f0-9]{10}$/', $orderNo)) {
            throw new AiException('官网返回的支付地址无效', AiException::UPSTREAM, self::rechargeUrl());
        }
        return ['order_no' => $orderNo, 'payurl' => $payurl];
    }

    /** @return array{status:string} */
    public static function orderStatus(string $orderNo): array
    {
        $body = self::rechargeRequest('GET', 'api/ai/orderStatus?order_no=' . rawurlencode($orderNo));
        return ['status' => (string)($body['data']['status'] ?? '')];
    }

    private static function rechargeRequest(string $method, string $path, array $data = []): array
    {
        $account = self::account();
        try {
            $options = [
                'headers' => ['token' => $account['token'], 'Accept' => 'application/json'],
            ];
            if ($data !== []) {
                $options['form_params'] = $data;
            }
            $response = (new Client(['timeout' => 15, 'connect_timeout' => 5]))->request(
                $method,
                rtrim((string)config('badouadmin.api_url'), '/') . '/' . $path,
                $options
            );
            $body = json_decode((string)$response->getBody(), true);
        } catch (\Throwable $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException
                && $e->getResponse()?->getStatusCode() === 401) {
                throw new AiException('badoucms账号登录已失效，请重新登录', AiException::NOT_LOGIN, self::rechargeUrl(), $e);
            }
            throw new AiException('官网充值服务暂不可用', AiException::UPSTREAM, self::rechargeUrl(), $e);
        }
        if (!is_array($body) || (int)($body['code'] ?? 0) !== 1) {
            $message = (string)($body['msg'] ?? '官网充值服务暂不可用');
            $reason = preg_match('/login|登录/i', $message) ? AiException::NOT_LOGIN : AiException::UPSTREAM;
            throw new AiException($message, $reason, self::rechargeUrl());
        }
        return $body;
    }

    /**
     * 官网 token 只用于领取会员密钥，后续模型请求直接到 NewAPI。
     */
    public static function provider(string $scene = 'default', float $timeout = 120, ?callable $onRetry = null, ?callable $checkRunning = null): AIProviderInterface
    {
        $checkRunning && $checkRunning();
        $account = self::account();
        $credential = self::credential($account);
        $checkRunning && $checkRunning();
        $client = $checkRunning !== null
            ? new CancellableHttpClient($checkRunning, $timeout)
            : new GuzzleHttpClient(timeout: $timeout, connectTimeout: 10);

        return (new AiModelProvider(
            baseUri: rtrim($credential['base_url'], '/') . '/v1',
            key: $credential['key'],
            model: $credential['model'],
            httpClient: $client,
        ))->onRetry($onRetry)->onCheck($checkRunning);
    }

    /** @param array{uid:int,token:string} $account
     *  @return array{base_url:string,key:string,model:string}
     */
    private static function credential(array $account): array
    {
        // 旧缓存领取时未同步模型白名单，升级后重新领取一次凭证。
        $cacheKey = 'agent_ai_credential_v2_' . $account['uid'] . '_' . hash('sha256', $account['token']);
        // 只缓存密钥，每轮核对会员当前配置，后台换模型后下一轮即可生效。
        try {
            $response = (new Client(['timeout' => 15, 'connect_timeout' => 5]))->get(
                rtrim((string)config('badouadmin.api_url'), '/') . '/api/ai/model',
                ['headers' => ['token' => $account['token'], 'Accept' => 'application/json']]
            );
            $body = json_decode((string)$response->getBody(), true);
        } catch (\Throwable $e) {
            throw new AiException('暂时无法读取 AI 模型设置，请稍后重试', AiException::UPSTREAM, self::rechargeUrl(), $e);
        }
        $selectedModel = (string)($body['data']['model'] ?? '');
        if (($body['code'] ?? 0) !== 1 || $selectedModel === '') {
            throw new AiException((string)($body['msg'] ?? '官网尚未提供 AI 模型设置，请更新官网服务'), AiException::UPSTREAM, self::rechargeUrl());
        }
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && isset($cached['base_url'], $cached['key'], $cached['model']) && $cached['model'] === $selectedModel) {
            return $cached;
        }

        try {
            $response = (new Client(['timeout' => 15, 'connect_timeout' => 5]))->get(
                rtrim((string)config('badouadmin.api_url'), '/') . '/' . self::CREDENTIAL_PATH,
                ['headers' => ['token' => $account['token'], 'Accept' => 'application/json']]
            );
            $body = json_decode((string)$response->getBody(), true);
        } catch (\Throwable $e) {
            throw new AiException('暂时无法获取 AI 访问凭证，请稍后重试', AiException::UPSTREAM, self::rechargeUrl(), $e);
        }
        $data = is_array($body) ? ($body['data'] ?? []) : [];
        if (($body['code'] ?? 0) !== 1 || !is_array($data)) {
            throw new AiException((string)($body['msg'] ?? 'AI 服务暂不可用'), AiException::UPSTREAM, self::rechargeUrl());
        }
        $credential = [
            'base_url' => (string)($data['base_url'] ?? ''),
            'key' => (string)($data['key'] ?? ''),
            'model' => (string)($data['model'] ?? ''),
        ];
        $url = parse_url($credential['base_url']);
        $scheme = strtolower((string)($url['scheme'] ?? ''));
        $host = strtolower((string)($url['host'] ?? ''));
        $isLocalHttp = $scheme === 'http' && in_array($host, ['localhost', '127.0.0.1', '[::1]', '::1'], true);
        if (($scheme !== 'https' && !$isLocalHttp) || $host === '' || $credential['key'] === '' || $credential['model'] === '') {
            throw new AiException('官网返回的 AI 配置不完整', AiException::UPSTREAM, self::rechargeUrl());
        }
        Cache::set($cacheKey, $credential, 300);
        return $credential;
    }

    /** 把网关资源、积分或登录错误翻成提示；其他错误原样抛出。 */
    public static function explain(\Throwable $e): \Throwable
    {
        if ($e instanceof AiException) return $e;
        $message = $e->getMessage();
        if (preg_match('/non-existing tool:\s*([a-zA-Z0-9_-]+)/i', $message, $tool)) {
            return new AiException('模型调用了当前未加载或无权使用的工具：' . $tool[1] . '。请重试；助手需要先查找可用工具，再执行操作。', AiException::UPSTREAM, self::rechargeUrl(), $e);
        }
        $payload = [];
        if (preg_match('/\{.*\}/s', $message, $match) === 1) {
            $decoded = json_decode($match[0], true);
            if (is_array($decoded)) {
                $payload = $decoded['error'] ?? $decoded;
            }
        }
        $code = (string)($payload['code'] ?? '');
        $text = (string)($payload['message'] ?? '');
        // NewAPI 的资源保护发生在模型转发之前，换模型无法解决。
        if (preg_match('/system_(disk|memory|cpu)_overloaded|system (disk|memory|cpu) overloaded/i', $code . ' ' . ($text !== '' ? $text : $message), $resource)) {
            $name = ['disk' => '磁盘', 'memory' => '内存', 'cpu' => 'CPU'][$resource[1] !== '' ? strtolower($resource[1]) : strtolower($resource[2])];
            $hint = 'AI 网关服务器' . $name . '使用率超过保护阈值';
            if (preg_match('/current:\s*([\d.]+)%,\s*threshold:\s*([\d.]+)%/i', $text !== '' ? $text : $message, $usage)) {
                $hint .= '（当前 ' . $usage[1] . '%，阈值 ' . $usage[2] . '%）';
            }
            $hint .= $name === '磁盘' ? '，请管理员清理网关服务器磁盘空间后重试。' : '，请管理员检查网关服务器负载后重试。';
            return new AiException($hint, AiException::UPSTREAM, self::rechargeUrl(), $e);
        }
        if (str_contains($text !== '' ? $text : $message, 'no access to model')) {
            // 白名单被管理员调整时，下次请求重新领取凭证并同步官网所选模型。
            try {
                $account = self::account();
                Cache::delete('agent_ai_credential_v2_' . $account['uid'] . '_' . hash('sha256', $account['token']));
            } catch (\Throwable) {
            }
            return new AiException('AI 模型权限尚未同步，已清除旧凭证，请重试', AiException::UPSTREAM, self::rechargeUrl(), $e);
        }
        if ($code === AiException::NOT_LOGIN || str_contains($message, 'HTTP 401')) {
            return new AiException($text !== '' ? $text : '官网登录已失效，请重新登录', AiException::NOT_LOGIN, self::rechargeUrl(), $e);
        }
        if ($code === AiException::NO_CREDIT || $code === 'insufficient_user_quota' || $code === 'insufficient_quota'
            || $code === 'pre_consume_token_quota_failed' || str_contains($message, 'HTTP 402')
            || str_contains($message, 'insufficient quota') || str_contains($message, 'quota is not enough')
            || str_contains($message, '额度不足')) {
            // 预扣额度包含历史和工具提示，余额大于零也可能不足以发起下一轮。
            $hint = 'AI 额度不足以支付本次请求，请购买 AI 额度后重试';
            if (preg_match('/token remain quota:\s*([¥￥$]?\d+(?:\.\d+)?),\s*need quota:\s*([¥￥$]?\d+(?:\.\d+)?)/u', $text !== '' ? $text : $message, $amounts)) {
                $hint = 'AI 额度不足：剩余 ' . $amounts[1] . '，本次请求需预扣 ' . $amounts[2] . '。请购买 AI 额度后重试';
            }
            return new AiException(
                $hint,
                AiException::NO_CREDIT,
                (string)($payload['recharge_url'] ?? self::rechargeUrl()),
                $e
            );
        }
        $hint = null;
        if ($code === 'do_request_failed' || preg_match('/upstream error|HTTP (500|502|503|504)\b/i', $message)) {
            $hint = 'AI 网关请求上游模型失败，请稍后重试；持续出现时请管理员检查模型服务连接和网关日志。';
        } elseif (preg_match('/timed? out|timeout|cURL error 28/i', $message)) {
            $hint = 'AI 模型请求超时，请稍后重试。';
        } elseif (preg_match('/HTTP 429\b|rate limit|too many requests/i', $message)) {
            $hint = 'AI 模型请求过于频繁或已达到限流上限，请稍后重试。';
        } elseif (preg_match('/connection refused|could not connect|could not resolve|cURL error (6|7)\b/i', $message)) {
            $hint = '无法连接 AI 服务，请管理员检查服务地址、网络和服务运行状态。';
        } elseif (preg_match('/model.{0,40}busy|busy.{0,40}model|overloaded|temporarily unavailable/i', $message)) {
            $hint = '当前 AI 模型繁忙或暂不可用，请稍后重试或切换其他可用模型。';
        } elseif ($message === 'The stream ended before the answer was complete.') {
            $hint = '模型连接提前中断，回答尚未完成，请稍后重试。';
        }
        if ($hint !== null) return new AiException($hint, AiException::UPSTREAM, self::rechargeUrl(), $e);
        return $e;
    }

}

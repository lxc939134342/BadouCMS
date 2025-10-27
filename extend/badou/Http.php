<?php

namespace badou;

use think\Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

class Http
{
    /**
     * 获取请求对象
     * @return Client
     */
    public static function getClient()
    {
        if (!extension_loaded('curl') && !ini_get('allow_url_fopen')) {
            throw new Exception('cURL extension is not installed and allow_url_fopen is disabled');
        }

        $options = [
            'base_uri'        => '',
            'timeout'         => 30,
            'connect_timeout' => 30,
            'verify'          => false,
            'http_errors'     => false,
            'headers'         => [
                'Referer'          => dirname(request()->root(true)),
                'User-Agent'       => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36',
            ]
        ];
        static $client;
        if (empty($client)) {
            $client = new Client($options);
        }
        return $client;
    }

    /**
     * 发送请求
     * @return array
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function sendRequest($url, $params = [], $method = 'POST')
    {
        $json = [];
        try {
            $client = self::getClient();
            $options = strtoupper($method) == 'POST' ? ['form_params' => $params] : ['query' => $params];

            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $content = $body->getContents();
            $json = (array)json_decode($content, true);
        } catch (TransferException $e) {
            // 输出更详细的错误信息用于调试
            throw new Exception(__('Network error') . ': ' . $e->getMessage());
        } catch (\Exception $e) {
            // 输出更详细的错误信息用于调试
            throw new Exception(__('Unknown data format') . ': ' . $e->getMessage());
        }
        return $json;
    }
}

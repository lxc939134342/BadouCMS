<?php
namespace app;

// 应用请求对象类
class Request extends \think\Request
{
    /**
     * 全局过滤规则
     * app/common.php 的 filter 函数
     */
    protected $filter = 'filter';

    /**
     * CDN 回源为 HTTP、但外部访问地址已配置为 HTTPS 时，
     * 按外部访问协议生成带域名的 URL。
     */
    public function isSsl(): bool
    {
        if (parent::isSsl()) {
            return true;
        }

        return strtolower((string) parse_url((string) config('badouadmin.app_url'), PHP_URL_SCHEME)) === 'https';
    }
}

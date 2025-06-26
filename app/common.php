<?php

// 应用公共文件
use think\Response;
use think\facade\App;
use think\facade\Lang;
use think\facade\Event;
use think\facade\Config;
use voku\helper\AntiXSS;
use app\admin\model\Config as configModel;
use think\exception\HttpResponseException;

// 简化目录分割线写法
!defined('DS') && define('DS', DIRECTORY_SEPARATOR);


function p($vars, $halt = 0)
{
    if (is_array($vars)) {
        echo '<pre>';
        print_r($vars);
        echo '</pre>';
    } else {
        var_dump($vars);
    }
    if ($halt) {
        throw new HttpResponseException(Response::create());
    }
}

if (!function_exists('__')) {

    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param string | array  $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __(string $name, array $vars = [], string $lang = ''): mixed
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        return Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('check_cors_request')) {
    /**
     * 跨域检测
     */
    function check_cors_request()
    {
        if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] && config('badouadmin.cors_request_domain')) {
            $info = parse_url($_SERVER['HTTP_ORIGIN']);
            $domainArr = explode(',', config('badouadmin.cors_request_domain'));
            $domainArr[] = request()->host(true);
            if (in_array("*", $domainArr) || in_array($_SERVER['HTTP_ORIGIN'], $domainArr) || (isset($info['host']) && in_array($info['host'], $domainArr))) {
                header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
            } else {
                $response = Response::create('跨域检测无效', 'html', 403);
                throw new HttpResponseException($response);
            }

            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');

            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
                }
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                    header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
                }
                $response = Response::create('', 'html');
                throw new HttpResponseException($response);
            }
        }
    }
}

if (!function_exists('filter')) {
    /**
     * 输入过滤
     * 富文本反XSS请使用 xss_clean，也就不需要及不能再 filter 了
     * @param string $string 要过滤的字符串
     * @return string
     */
    function filter(string $string): string
    {
        // 去除字符串两端空格（对防代码注入有一定作用）
        $string = trim($string);

        // 过滤html和php标签
        $string = strip_tags($string);

        // 特殊字符转实体
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
    }
}

if (!function_exists('htmlspecialchars_decode_improve')) {
    /**
     * html解码增强
     * 被 filter函数 内的 htmlspecialchars 编码的字符串，需要用此函数才能完全解码
     * @param string $string
     * @param int    $flags
     * @return string
     */
    function htmlspecialchars_decode_improve(string $string, int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401): string
    {
        return htmlspecialchars_decode($string, $flags);
    }
}

if (!function_exists('xss_clean')) {
    /**
     * 清理XSS
     * 通常只用于富文本，比 filter 慢
     * @param string $string
     * @return string
     */
    function xss_clean(string $string): string
    {
        $antiXss = new AntiXSS();

        // 允许 style 属性（style="list-style-image: url(javascript:alert(0))" 任然可被正确过滤）
        $antiXss->removeEvilAttributes(['style']);

        // 检查到 xss 代码之后使用 cleanXss 替换它
        $antiXss->setReplacement('cleanXss');

        return $antiXss->xss_clean($string);
    }
}

if (!function_exists('url_clean')) {
    /**
     * 清理URL
     */
    function url_clean($url)
    {
        if (!check_url_allowed($url)) {
            return '';
        }
        return xss_clean($url);
    }
}

if (!function_exists('check_ip_allowed')) {
    /**
     * 检测IP是否允许
     * @param string $ip IP地址
     */
    function check_ip_allowed($ip = null)
    {
        $ip = is_null($ip) ? request()->ip() : $ip;
        $forbiddenipArr = config('site.forbiddenip');
        $forbiddenipArr = !$forbiddenipArr ? [] : $forbiddenipArr;
        $forbiddenipArr = is_array($forbiddenipArr) ? $forbiddenipArr : array_filter(explode("\n", str_replace("\r\n", "\n", $forbiddenipArr)));
        if ($forbiddenipArr && \Symfony\Component\HttpFoundation\IpUtils::checkIp($ip, $forbiddenipArr)) {
            $response = Response::create('请求无权访问', 'html', 403);
            throw new HttpResponseException($response);
        }
    }
}

if (!function_exists('check_url_allowed')) {
    /**
     * 检测URL是否允许
     * @param string $url URL
     * @return bool
     */
    function check_url_allowed($url = '')
    {
        //允许的主机列表
        $allowedHostArr = [
            strtolower(request()->host())
        ];

        if (empty($url)) {
            return true;
        }

        //如果是站内相对链接则允许
        if (preg_match("/^[\/a-z][a-z0-9][a-z0-9\.\/]+((\?|#).*)?\$/i", $url) && substr($url, 0, 2) !== '//') {
            return true;
        }

        //如果是站外链接则需要判断HOST是否允许
        if (preg_match("/((http[s]?:\/\/)+((?>[a-z\-0-9]{2,}\.)+[a-z]{2,8}|((?>([0-9]{1,3}\.)){3}[0-9]{1,3}))(:[0-9]{1,5})?)(?:\s|\/)/i", $url)) {
            $chkHost = parse_url(strtolower($url), PHP_URL_HOST);
            if ($chkHost && in_array($chkHost, $allowedHostArr)) {
                return true;
            }
        }

        return false;
    }
}


if (!function_exists('cdnurl')) {

    /**
     * 获取上传资源的CDN的地址
     * @param string  $url    资源相对地址
     * @param boolean $domain 是否显示域名 或者直接传入域名
     * @return string
     */
    function cdnurl($url, $domain = false)
    {
        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        $cdnurl = Config::get('upload.cdnurl');
        if (is_bool($domain) || stripos($cdnurl, '/') === 0) {
            $url = preg_match($regex, $url) || ($cdnurl && stripos($url, $cdnurl) === 0) ? $url : $cdnurl . $url;
        }
        if ($domain && !preg_match($regex, $url)) {
            $domain = is_bool($domain) ? request()->domain() : $domain;
            $url = $domain . $url;
        }
        return $url;
    }
}

if (!function_exists('full_url')) {

    /**
     * 获取资源完整url地址；若安装了云存储或 config/badouadmin.php 配置了CdnUrl，则自动使用对应的CdnUrl
     * @param string      $relativeUrl 资源相对地址 不传入则获取域名
     * @param string|bool $domain      是否携带域名 或者直接传入域名
     * @param string      $default     默认值
     * @return string
     */
    function full_url(string $relativeUrl = '', string|bool $domain = true, string $default = ''): string
    {
        // 存储/上传资料配置
        Event::trigger('uploadConfigInit', App::getInstance());

        $cdnUrl = Config::get('badouadmin.cdn_url');
        if (!$cdnUrl) {
            $cdnUrl = request()->upload['cdn'] ?? '//' . request()->host();
        }
        if ($domain === true) {
            $domain = $cdnUrl;
        } elseif ($domain === false) {
            $domain = '';
        }

        $relativeUrl = $relativeUrl ?: $default;
        if (!$relativeUrl) {
            return $domain;
        }

        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        if (preg_match('/^http(s)?:\/\//', $relativeUrl) || preg_match($regex, $relativeUrl) || $domain === false) {
            return $relativeUrl;
        }

        $url          = $domain . $relativeUrl;
        $cdnUrlParams = Config::get('buildadmin.cdn_url_params');
        if ($domain === $cdnUrl && $cdnUrlParams) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url       .= $separator . $cdnUrlParams;
        }

        return $url;
    }
}


if (!function_exists('letter_avatar')) {
    /**
     * 首字母头像
     * @param $text
     * @return string
     */
    function letter_avatar($text)
    {
        $total = unpack('L', hash('adler32', $text, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

        $bg = "rgb({$r},{$g},{$b})";
        $color = "#ffffff";
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="' . $bg . '" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="50" text-copy="fast" fill="' . $color . '" text-anchor="middle" text-rights="admin" dominant-baseline="central">' . $first . '</text></svg>');
        $value = 'data:image/svg+xml;base64,' . $src;
        return $value;
    }
}

if (!function_exists('hsv2rgb')) {
    function hsv2rgb($h, $s, $v)
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255)
        ];
    }
}

if (!function_exists('get_sys_config')) {

    /**
     * 获取站点的系统配置，不传递参数则获取所有配置项
     * @param string $name    变量名
     * @param string $group   变量分组，传递此参数来获取某个分组的所有配置项
     * @param bool   $concise 是否开启简洁模式，简洁模式下，获取多项配置时只返回配置的键值对
     * @return mixed
     * @throws Throwable
     */
    function get_sys_config(string $name = '', string $group = '', bool $concise = true): mixed
    {
        if ($name) {
            // 直接使用->value('value')不能使用到模型的类型格式化
            $config = configModel::cache($name, null, configModel::$cacheTag)->where('name', $name)->find();
            if ($config) {
                $config = $config['value'];
            }
        } else {
            if ($group) {
                $temp = configModel::cache('group' . $group, null, configModel::$cacheTag)->where('group', $group)->select()->toArray();
            } else {
                $temp = configModel::cache('sys_config_all', null, configModel::$cacheTag)->order('weigh desc')->select()->toArray();
            }
            if ($concise) {
                $config = [];
                foreach ($temp as $item) {
                    $config[$item['name']] = $item['value'];
                }
            } else {
                $config = $temp;
            }
        }
        return $config;
    }
}

if (!function_exists('str_attr_to_array')) {

    /**
     * 将字符串属性列表转为数组
     * @param string $attr 属性，一行一个，无需引号，比如：class=input-class
     * @return array
     */
    function str_attr_to_array(string $attr): array
    {
        if (!$attr) {
            return [];
        }
        $attr     = explode("\n", trim(str_replace("\r\n", "\n", $attr)));
        $attrTemp = [];
        foreach ($attr as $item) {
            $item = explode('=', $item);
            if (isset($item[0]) && isset($item[1])) {
                $attrVal = $item[1];
                if ($item[1] === 'false' || $item[1] === 'true') {
                    $attrVal = !($item[1] === 'false');
                } elseif (is_numeric($item[1])) {
                    $attrVal = (float)$item[1];
                }
                if (strpos($item[0], '.')) {
                    $attrKey = explode('.', $item[0]);
                    if (isset($attrKey[0]) && isset($attrKey[1])) {
                        $attrTemp[$attrKey[0]][$attrKey[1]] = $attrVal;
                        continue;
                    }
                }
                $attrTemp[$item[0]] = $attrVal;
            }
        }
        return $attrTemp;
    }
}


if (!function_exists('keys_to_camel_case')) {

    /**
     * 将数组 key 的命名方式转换为小写驼峰
     * @param array $array 被转换的数组
     * @param array $keys  要转换的 key，默认所有
     * @return array
     */
    function keys_to_camel_case(array $array, array $keys = []): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            // 将键名转换为驼峰命名
            $camelCaseKey = $keys && in_array($key, $keys) ? parse_name($key, 1, false) : $key;

            if (is_array($value)) {
                // 如果值是数组，递归转换
                $result[$camelCaseKey] = keys_to_camel_case($value);
            } else {
                $result[$camelCaseKey] = $value;
            }
        }
        return $result;
    }
}

if (!function_exists('check_nav_active')) {
    /**
     * 检测会员中心导航是否高亮
     */
    function check_nav_active($url, $classname = 'active')
    {
        $auth = \app\common\library\FrontendAuth::instance();
        $requestUrl = $auth->getRequestUri();
        $url = ltrim($url, '/');
        return $requestUrl === str_replace(".", "/", $url) ? $classname : '';
    }
}

if (!function_exists('hook')) {
    /**
     * 处理插件钩子
     * @param string $hook        钩子名称
     * @param mixed $params       传入参数
     * @param  boolean $is_return 是否返回（true:返回值，false:直接输入）
     * @param  bool   $once       只获取一个有效返回值
     * @return void
     */
    function hook($hook, $params = null, $is_return = false, $once = false)
    {
        if ($is_return == true) {
            return Event::trigger($hook, $params, $once);
        }
        Event::trigger($hook, $params, $once);
    }
}

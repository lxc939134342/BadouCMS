<?php

// 应用公共文件
use think\Response;
use think\facade\App;
use think\facade\Lang;
use think\facade\Event;
use think\facade\Config;
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

        // 过滤php标签
        $string = strip_only_php($string);

        // 特殊字符转实体
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
    }
}

if (!function_exists('strip_only_php')) {
    /**
     * 过滤php标签
     * @param string $string
     * @return array|string|null
     */
    function strip_only_php(string $string): string
    {
        /**
         * 移除所有PHP代码块：
         * - <?php ... ?> (标准标签)
         * - <?= ... ?> (短回显标签)
         * - <? ... ?> (短开放标签，排除 <?xml, <?php, <?=)
         * /s 修正符让 . 匹配包括换行符在内的所有字符
         * /i 修正符让匹配不区分大小写 (虽然PHP标签通常是小写，但以防万一)
         */
        $string = preg_replace('/<\?(?:php|=)?(?!xml)[^?]*?\?>/si', '', $string);

        // 移除 {$xxxx} 格式的变量插值
        $string = preg_replace('/\{\$[a-zA-Z_][a-zA-Z0-9_]*\}/', '', $string);

        return $string;
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
        try {
            $config = \HTMLPurifier_Config::createDefault();

            /* ---------- 1. 黑名单：禁用高危标签/属性 ---------- */
            $config->set('HTML.ForbiddenElements',  ['script', 'object', 'embed', 'frame', 'frameset']);
            $config->set('HTML.ForbiddenAttributes', ['on*']);

            /* ---------- 2. 注册 HTML5 <video> / <source> ---------- */
            $config->set('HTML.DefinitionID', 'html5-video');
            $config->set('HTML.DefinitionRev', 1);
            if ($def = $config->maybeGetRawHTMLDefinition()) {
                // 空元素 <source>
                $def->addElement('source', 'Inline', 'Empty', 'Common', [
                    'src'  => 'URI',
                    'type' => 'Text',
                ]);

                // 块级元素 <video>
                $def->addElement('video', 'Block', 'Optional: #PCDATA | source', 'Common', [
                    'src'      => 'URI',
                    'controls' => 'CDATA',   // 支持 controls=""
                    'preload'  => 'Enum#auto,metadata,none',
                    'width'    => 'Length',
                    'height'   => 'Length',
                    'class'    => 'CDATA',
                    'id'       => 'ID',
                    'data-setup' => 'CDATA',
                ]);

                /* ---------- 3. 一次性注册常用 HTML5 标签 ---------- */
                $html5Block = ['section', 'article', 'aside', 'header', 'footer', 'nav', 'main', 'figure', 'figcaption', 'details', 'summary'];
                $html5Inline = ['mark', 'time', 'wbr', 'meter', 'progress'];

                foreach ($html5Block as $tag) {
                    $def->addElement($tag, 'Block', 'Flow', 'Common');
                }
                foreach ($html5Inline as $tag) {
                    $def->addElement($tag, 'Inline', 'Inline', 'Common');
                }

                /* ---------- 4. 给常用元素批量追加 data-* 属性 ---------- */
                $commonElements = array_merge(
                    ['video', 'source'],
                    $html5Block,
                    $html5Inline,
                    ['div', 'span', 'a', 'img', 'p', 'li', 'td', 'th', 'button']
                );
                $dataAttrs = ['data-id', 'data-toggle', 'data-url', 'data-target', 'data-key', 'data-value']; // 按需继续补

                foreach ($commonElements as $tag) {
                    foreach ($dataAttrs as $attr) {
                        $def->addAttribute($tag, $attr, 'CDATA');
                    }
                }
            }

            $purifier = new \HTMLPurifier($config);

            $clean_html = $purifier->purify($string);
        } catch (Exception $e) {
            trace($e->getMessage(), 'error');
            return '';
        }
        return $clean_html;
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
        $forbiddenipArr = get_sys_config('no_access_ip');
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
        if (preg_match("/^[\/a-z][a-z0-9][a-z0-9\.\/\_]+((\?|#).*)?\$/i", $url) && substr($url, 0, 2) !== '//') {
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
     * @param boolean $cdn    是否使用CDN地址
     * @return string
     */
    function cdnurl($url, $domain = false, $cdn = true)
    {
        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        $cdnurl = $cdn ? Config::get('upload.cdnurl') : '';
        if (is_bool($domain) || (isset($cdnurl) && stripos($cdnurl, '/') === 0)) {
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

if (!function_exists('format_bytes')) {
    /**
     * 字节转单位
     * @param mixed $data
     * @return string
     */
    function format_bytes($data): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB'];
        for ($i = 0; $data >= 1024 && $i < count($units); $i++) {
            $data /= 1024;
        }
        return round($data, 2) . $units[$i];
    }
}

if (!function_exists('hook')) {
    /**
     * 处理插件钩子
     * @param string $hook        钩子名称
     * @param mixed $params       传入参数
     * @param  boolean $is_return 是否返回（true:返回值，false:直接输入）
     * @param  bool   $once       只获取一个有效返回值
     * @return void|array
     */
    function hook($hook, $params = null, $is_return = false, $once = false)
    {
        if ($is_return == true) {
            return Event::trigger($hook, $params, $once);
        }
        Event::trigger($hook, $params, $once);
    }
}

if (!function_exists('has_hook')) {
    /**
     * 检查是否存在某个钩子（事件）的监听器
     * @param string $hook        钩子名称
     * @return bool               存在监听器返回 true，否则返回 false
     */
    function has_hook($hook): bool
    {
        return Event::hasListener($hook);
    }
}

if (!function_exists('convert_path_to_snake_case')) {
    /**
     * 将路径中的控制器部分从驼峰命名转换为下划线命名
     * 例如：cms.ContentSort/index -> cms.content_sort/index
     * 例如：Abc/index -> abc/index
     * 例如：Abc -> abc
     * @param string $path 原始路径字符串
     * @return string 转换后的路径字符串
     */
    function convert_path_to_snake_case(string $path): string
    {
        // 分割路径为控制器部分和动作部分
        $parts = explode('/', $path, 2);
        $controllerOrModule = $parts[0]; // 可能是 "cms.ContentSort" 或 "Abc"
        $action = $parts[1] ?? ''; // 动作部分可能不存在

        // 确保引入 ThinkPHP 的 Str 类
        if (!class_exists(\think\helper\Str::class)) {
            // 如果 Str 类不存在，则无法进行转换，或者需要手动实现驼峰转下划线逻辑
            // 对于没有 Str 类的情况，我们只能尝试转换为小写
            if (str_contains($controllerOrModule, '.')) {
                // 如果有模块名，但没有 Str 类，则无法正确转换控制器名，返回原路径
                return $path;
            } else {
                // 如果没有模块名，直接转换为小写
                return strtolower($path);
            }
        }

        $newControllerOrModule = '';
        if (str_contains($controllerOrModule, '.')) {
            // 包含模块名和控制器名，例如 "cms.ContentSort"
            $controllerParts = explode('.', $controllerOrModule, 2);
            $module = $controllerParts[0];
            $controllerName = $controllerParts[1] ?? '';
            $snakeCaseControllerName = \think\helper\Str::snake($controllerName);
            $newControllerOrModule = $module . '.' . $snakeCaseControllerName;
        } else {
            // 不包含模块名，直接将整个部分视为控制器名并转换为下划线命名
            // 例如 "Abc" -> "abc", "Dash" -> "dash"
            $newControllerOrModule = \think\helper\Str::snake($controllerOrModule);
        }

        // 重新组合路径
        $newPath = $newControllerOrModule;
        if ($action !== '') {
            $newPath .= '/' . $action;
        }

        return $newPath;
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

if (!function_exists('parse_array')) {
    /** 解析"1:1\r\n2:3"格式字符串为数组
     * @param $value
     * @param bool $is_arr_kv 是否返回键值对数组
     * @return array
     */
    function parse_array($value, $is_arr_kv = false, $delimiter = '[,，;；\r\n]+')
    {
        $data = [];

        // 解码HTML实体，防止 &amp; 这里的分号被误当做分隔符
        if (is_string($value)) {
            $value = htmlspecialchars_decode_improve($value);
        }

        // 去掉前后空格
        $trimmed = trim($value);

        // 将两端的自定义分隔符也去掉（例如开头或者结尾刚好有逗号）
        $trimmed = preg_replace('/^' . $delimiter . '|' . $delimiter . '$/u', '', $trimmed);
        // 使用正则分割字符串
        $array = preg_split('/\s*' . $delimiter . '\s*/u', $trimmed, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($array as $val) {
            // 将中文全角冒号统一替换为英文半角冒号，方便后续统一处理
            $val = str_replace('：', ':', $val);
            if (strpos($val, ':') !== false) {
                list($k, $v) = explode(':', $val, 2);
                if ($is_arr_kv) {
                    $data[] = ['key' => $k, 'value' => $v];
                } else {
                    $data[$k] = $v;
                }
            } else {
                $data[$val] = $val;
            }
        }

        return $data;
    }
}

if (!function_exists('parse_array_string')) {
    /**
     * 数组转换成换行字符串
     * @param $array
     * @return string
     */
    function parse_array_string($array)
    {
        if (is_array($array)) {
            $lines = [];
            foreach ($array as $k => $v) {
                $lines[] = $k . ':' . $v;
            }
            return implode("\r\n", $lines);
        } else {
            return $array;
        }
    }
}

if (!function_exists('array_to_value')) {
    /**
     * 获取数组中指定元素的值
     * @param mixed $array
     * @param mixed $index
     */
    function array_to_value($array, $index = 0)
    {
        if (empty($array)) {
            return '';
        }
        if (!is_array($array)) {
            return $array;
        }
        if (isset($array[$index])) {
            return $array[$index];
        }
        return '';
    }
}

if (!function_exists('seconds_to_human')) {
    /**
     * 将秒数转换为人易读的字符串（支持指定计算单位）
     * @param int $seconds 秒数
     * @param string $unit 指定单位（年, 月, 周, 天, 小时, 分钟, 秒）
     * @return string
     */
    function seconds_to_human(int $seconds, $unit = ''): string
    {
        if ($seconds <= 0) {
            return '0' . ($unit ?: '秒');
        }

        $units = [
            '年'   => 31536000,
            '月'   => 2592000,
            '周'   => 604800,
            '天'   => 86400,
            '小时' => 3600,
            '分钟' => 60,
            '秒'   => 1,
        ];

        // 如果指定了单位且在列表中
        if ($unit && isset($units[$unit])) {
            $num = $seconds / $units[$unit];
            // 如果是整数则输出整数，否则保留两位小数
            $num = (floor($num) == $num) ? (int)$num : round($num, 2);
            return $num . $unit;
        }

        // 默认逻辑：按最大单位递进
        $units_desc = [
            31536000 => '年',
            2592000  => '月',
            604800   => '周',
            86400    => '天',
            3600     => '小时',
            60       => '分钟',
            1        => '秒',
        ];
        $result = '';
        foreach ($units_desc as $key => $value) {
            if ($seconds >= $key) {
                $num = floor($seconds / $key);
                $seconds %= $key;
                $result .= $num . $value;
            }
        }
        return $result;
    }
}


if (!function_exists('build_suffix_image')) {
    /**
     * 生成文件后缀图片
     * @param string $suffix 后缀
     * @param null   $background
     * @return string
     */
    function build_suffix_image($suffix, $background = null)
    {
        $suffix = mb_substr(strtoupper($suffix), 0, 4);
        $total = unpack('L', hash('adler32', $suffix, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

        $background = $background ? $background : "rgb({$r},{$g},{$b})";

        $icon = <<<EOT
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
            <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
            <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
            <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
            <path style="fill:{$background};" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16 V416z"/>
            <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
            <g><text><tspan x="220" y="380" font-size="124" font-family="Verdana, Helvetica, Arial, sans-serif" fill="white" text-anchor="middle">{$suffix}</tspan></text></g>
        </svg>
EOT;
        return $icon;
    }
}

<?php

use think\facade\Db;
use badou\Filesystem;
use think\facade\Request;

if (!function_exists('replace_keyword')) {
    /**
     * 替换内容中的敏感词
     * @param string $content 内容
     * @return string 替换后的内容
     */
    function replace_keyword($content)
    {
        $keys = get_sys_config('content_keyword_replace');
        $keys_arr = explode(',', strip_tags($keys));
        foreach ($keys_arr as $key => $value) {
            $content = str_replace($value, str_repeat('*', mb_strlen($value)), $content);
        }
        return $content;
    }
}


/**
 * 获取用户浏览器类型
 * @param string|null $bs 传入字符串检测是否包含于User-Agent中，返回bool
 * @return string|bool|null 返回浏览器名称，传入参数时返回bool，User-Agent不存在时返回null
 */
function get_user_bs(?string $bs = null)
{
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return null;
    }

    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

    // 直接检测传递的值，严格判断strpos返回值
    if ($bs !== null) {
        return strpos($user_agent, strtolower($bs)) !== false;
    }

    // 浏览器关键字映射
    $browser_map = [
        'micromessenger' => 'Weixin',
        'qq'             => 'QQ',
        'weibo'          => 'Weibo',
        'alipayclient'   => 'Alipay',
        'trident/7.0'    => 'IE11', // 新版本IE优先，避免360等浏览器的兼容模式检测错误
        'trident/6.0'    => 'IE10',
        'trident/5.0'    => 'IE9',
        'trident/4.0'    => 'IE8',
        'msie 7.0'       => 'IE7',
        'msie 6.0'       => 'IE6',
        'edge'           => 'Edge',
        'firefox'        => 'Firefox',
        // chrome和android合并判断
        'chrome'         => 'Chrome',
        'android'        => 'Chrome',
        'safari'         => 'Safari',
        'mj12bot'        => 'MJ12bot',
    ];

    foreach ($browser_map as $key => $name) {
        if (strpos($user_agent, $key) !== false) {
            return $name;
        }
    }

    return 'Other';
}

// 获取用户操作系统类型
/**
 * 获取用户操作系统信息
 *
 * @param string|null $osstr 可选，检测指定操作系统关键字，返回布尔值
 * @return string|bool|null 返回操作系统名称，指定关键字检测时返回布尔值，HTTP_USER_AGENT不存在时返回null
 */
function get_user_os(?string $osstr = null)
{
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return null;
    }

    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

    if ($osstr !== null) {
        return strpos($user_agent, strtolower($osstr)) !== false;
    }

    $os_map = [
        'windows nt 10'   => 'Windows 10',
        'windows nt 6.3'  => 'Windows 8.1',
        'windows nt 6.2'  => 'Windows 8',
        'windows nt 6.1'  => 'Windows 7',
        'windows nt 6.0'  => 'Windows Vista',
        'windows nt 5.2'  => 'Windows 2003',
        'windows nt 5.1'  => 'Windows XP',
        'windows nt 5.0'  => 'Windows 2000',
        'windows nt 9'    => 'Windows 9X',
        'windows phone'   => 'Windows Phone',
        'android'        => 'Android',
        'iphone'         => 'iPhone',
        'ipad'           => 'iPad',
        'mac'            => 'Mac',
        'sunos'          => 'Sun OS',
        'bsd'            => 'BSD',
        'ubuntu'         => 'Ubuntu',
        'linux'          => 'Linux',
        'unix'           => 'Unix',
    ];

    foreach ($os_map as $key => $name) {
        if (strpos($user_agent, $key) !== false) {
            return $name;
        }
    }

    return 'Other';
}

// 获取用户IP
function get_user_ip(): string
{
    return Request::ip();
}

// 返回时间戳格式化日期时间，默认当前
function get_datetime($timestamp = null)
{
    if (! $timestamp) {
        $timestamp = time();
    }
    return date('Y-m-d H:i:s', $timestamp);
}

// 返回时间戳格式化日期，默认当前
function get_date($timestamp = null)
{
    if (! $timestamp) {
        $timestamp = time();
    }
    return date('Y-m-d', $timestamp);
}

// 返回时间戳差值部分，年、月、日
function get_date_diff($startstamp, $endstamp, $return = 'm')
{
    $y = date('Y', $endstamp) - date('Y', $startstamp);
    $m = date('m', $endstamp) - date('m', $startstamp);

    switch ($return) {
        case 'y':
            if ($y <= 1) {
                $y = $m / 12;
            }
            $string = $y;
            break;
        case 'm':
            $string = $y * 12 + $m;
            break;
        case 'd':
            $string = ($endstamp - $startstamp) / 86400;
            break;
    }
    return $string;
}

// 生成无限极树,$data为二维数组数据
function get_tree($data, $tid, $idField, $pidField, $sonName = 'son')
{
    $tree = array();
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            if ($value[$pidField] == "$tid") { // 父亲找到儿子
                $value[$sonName] = get_tree($data, $value[$idField], $idField, $pidField, $sonName);
                $tree[] = $value;
            }
        } else {
            if ($value->$pidField == "$tid") { // 父亲找到儿子
                $temp = clone $value;
                $temp->$sonName = get_tree($data, $value->$idField, $idField, $pidField, $sonName);
                $tree[] = $temp;
            }
        }
    }
    return $tree;
}

// 获取数据数组的映射数组
function get_mapping($array, $vValue, $vKey = null)
{
    if (! $array) {
        return array();
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            if ($vKey) {
                $result[$value[$vKey]] = $value[$vValue];
            } else {
                $result[] = $value[$vValue];
            }
        } elseif (is_object($value)) {
            if ($vKey) {
                $result[$value->$vKey] = $value->$vValue;
            } else {
                $result[] = $value->$vValue;
            }
        } else {
            return $array;
        }
    }
    return $result;
}

// 获取字符串第N次出现位置
function get_strpos($string, $find, $n)
{
    $pos = strpos($string, $find);
    for ($i = 2; $i <= $n; $i++) {
        $pos = strpos($string, $find, $pos + 1);
    }
    return $pos;
}

// 获取转义数据，支持字符串、数组、对象
function escape_string($string)
{
    if (! $string) {
        return $string;
    }
    if (is_array($string)) { // 数组处理
        foreach ($string as $key => $value) {
            $string[$key] = escape_string($value);
        }
    } elseif (is_object($string)) { // 对象处理
        foreach ($string as $key => $value) {
            $string->$key = escape_string($value);
        }
    } else { // 字符串处理
        $string = htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8') ?? '';
        $string = addslashes($string);
    }
    return $string;
}

function mark($string)
{
    if ($reqdata = request()->param('keyword')) {
        $string = preg_replace('/(' . $reqdata . ')/i', '<span style="color:red">$1</span>', $string);
    }
    return $string;
}

// 字符反转义html实体及斜杠，支持字符串、数组、对象
function decode_string($string)
{
    if (! $string) {
        return $string;
    }
    if (is_array($string)) { // 数组处理
        foreach ($string as $key => $value) {
            $string[$key] = decode_string($value);
        }
    } elseif (is_object($string)) { // 对象处理
        foreach ($string as $key => $value) {
            $string->$key = decode_string($value);
        }
    } else { // 字符串处理
        $string = stripcslashes($string);
        $string = htmlspecialchars_decode($string, ENT_QUOTES);
    }
    return $string;
}

// 字符反转义斜杠，支持字符串、数组、对象
function decode_slashes($string)
{
    if (! $string) {
        return $string;
    }
    if (is_array($string)) { // 数组处理
        foreach ($string as $key => $value) {
            $string[$key] = decode_slashes($value);
        }
    } elseif (is_object($string)) { // 对象处理
        foreach ($string as $key => $value) {
            $string->$key = decode_slashes($value);
        }
    } else { // 字符串处理
        $string = stripcslashes($string);
    }
    return $string;
}

// 字符串双层MD5加密
function encrypt_string($string)
{
    return md5(md5($string));
}

// 清洗html代码的空白符号
function clear_html_blank($string)
{
    $string = str_replace("\r\n", '', $string); // 清除换行符
    $string = str_replace("\n", '', $string); // 清除换行符
    $string = str_replace("\t", '', $string); // 清除制表符
    $string = str_replace('　', '', $string); // 清除大空格
    $string = str_replace('&nbsp;', '', $string); // 清除 &nbsp;
    $string = preg_replace('/\s+/', ' ', $string); // 清除空格
    return $string;
}

// 去除字符串两端斜线
function trim_slash($string)
{
    return trim($string, '/');
}

// 转换对象为数组
function object_to_array($object)
{
    if ($object === null) {
        return [];
    } else {
        return json_decode(json_encode($object), true);
    }
}

// 转换数组为对象
function array_to_object($array)
{
    return json_decode(json_encode($array));
}

// 值是否在对象中
function in_object($needle, $object)
{
    foreach ($object as $value) {
        if ($needle == $value) {
            return true;
        }
    }
}

// 结果集中查找指定字段父节点是否存在
function result_value_search($needle, $result, $skey)
{
    foreach ($result as $key => $value) {
        if ($value->$skey == $needle) {
            return $key;
        }
    }
    return false;
}

// 多维数组合并
function mult_array_merge($array1, $array2)
{
    if (is_array($array2)) {
        foreach ($array2 as $key => $value) {
            if (is_array($value)) {
                if (array_key_exists($key, $array1)) {
                    $array1[$key] = mult_array_merge($array1[$key], $value);
                } else {
                    $array1[$key] = $value;
                }
            } else {
                $array1[$key] = $value;
            }
        }
    }
    return $array1;
}

// 数组转换为带引号字符串
function implode_quot($glue, array $pieces, $diffnum = false)
{
    if (! $pieces) {
        return "''";
    }
    foreach ($pieces as $key => $value) {
        if ($diffnum && ! is_numeric($value)) {
            $value = "'$value'";
        } elseif (! $diffnum) {
            $value = "'$value'";
        }
        if (isset($string)) {
            $string .= $glue . $value;
        } else {
            $string = $value;
        }
    }
    return $string;
}

// 是否为多维数组,是返回true
function is_multi_array($array)
{
    if (is_array($array)) {
        return (count($array) != count($array, 1));
    } else {
        return false;
    }
}

// 获取间隔的月份的起始及结束日期
function get_month_days($date, $start = 0, $interval = 1, $retamp = false)
{
    $timestamp = strtotime($date) ?: $date;
    $first_day = strtotime(date('Y', $timestamp) . '-' . date('m', $timestamp) . '-01 +' . $start . ' month');
    $last_day = strtotime(date('Y-m-d', $first_day) . ' +' . $interval . ' month -1 day');
    if ($retamp) {
        $return = array(
            'first' => $first_day,
            'last' => $last_day
        );
    } else {
        $return = array(
            'first' => date('Y-m-d', $first_day),
            'last' => date('Y-m-d', $last_day)
        );
    }
    return $return;
}

/* 截取字符串 */
function len($string, $length)
{
    return substr_both($string, 0, $length);
}

// 截取字符串，中文算两个字符
function lencn($string, $length)
{
    if (strlen_both($string) >  $length) {
        return substr_both($string, 0, $length);
    }
    return $string;
}

/* 时间函数 */
function style($string, $style)
{
    // 如果不是时间戳，尝试转换为时间戳
    if (!is_numeric($string)) {
        $timestamp = strtotime($string);
        if ($timestamp === false) {
            return $string; // 如果转换失败，返回原字符串
        }
        $string = $timestamp;
    }

    return date($style, $string);
}

/**
 *  运算功能
 * @param mixed $data
 * @param mixed $value
 */
function operate($data, $value)
{
    if (preg_match('/^([\+\-\*\/\%])([0-9\.]+)$/', $value, $mathes)) {
        if (! is_numeric($data)) {
            $data = 0;
        }
        switch ($mathes[1]) {
            case '+':
                $data = $data + $mathes[2];
                break;
            case '-':
                $data = $data - $mathes[2];
                break;
            case '*':
                $data = $data * $mathes[2];
                break;
            case '/':
                $data = $data / $mathes[2];
                break;
            case '%':
                $data = $data % $mathes[2];
                break;
        }
    }
    return $data;
}

function dropblank($string)
{
    return clear_html_blank($string);
}

/**
 * 字符串截取 清除html标签
 * @param mixed $string 字符串
 * @param mixed $strat 起始位置
 * @param mixed $length 长度
 * @return string
 */
function bd_substr($string, $strat, $length)
{
    //先清除html标签
    $string = drophtml($string);
    return substr_both($string, $strat, $length);
}

/*清除html标签与换行*/
if (!function_exists('drophtml')) {
    function drophtml($str)
    {
        $str = strip_tags($str);
        $str = clear_html_blank($str);
        return $str;
    }
}

// 中英混合的字符串截取,以一个汉字为一个单位长度，英文为半个
function substr_both($string, $strat, $length)
{
    $s = 0; // 起始位置
    $i = 0; // 实际Byte计数
    $n = 0; // 字符串长度计数
    $str_length = strlen($string); // 字符串的字节长度
    while (($n < $length) and ($i < $str_length)) {
        $ascnum = Ord(substr($string, $i, 1)); // 得到字符串中第$i位字符的ascii码
        if ($ascnum >= 224) { // 根据UTF-8编码规范，将3个连续的字符计为单个字符
            $i += 3;
            $n++;
        } elseif ($ascnum >= 192) { // 根据UTF-8编码规范，将2个连续的字符计为单个字符
            $i += 2;
            $n++;
        } else {
            $i += 1;
            $n += 0.5;
        }
        if ($s == 0 && $strat > 0 && $n >= $strat) {
            $s = $i; // 记录起始位置
        }
    }
    if ($n < $strat) { // 起始位置大于字符串长度
        return;
    }
    // 如果截取的长度小于原字符串长度，则添加...
    $result = substr($string, $s, $i);
    if ($i < $str_length) {
        $result .= '...';
    }
    return $result;
}

// 中英混合的字符串长度,以一个汉字为一个单位长度，英文为半个
function strlen_both($string)
{
    $i = 0; // 实际Byte计数
    $n = 0; // 字符串长度计数
    $str_length = strlen($string); // 字符串的字节长度
    while ($i < $str_length) {
        $ascnum = Ord(substr($string, $i, 1)); // 得到字符串中第$i位字符的ascii码
        if ($ascnum >= 224) { // 根据UTF-8编码规范，将3个连续的字符计为单个字符
            $i += 3;
            $n++;
        } elseif ($ascnum >= 192) { // 根据UTF-8编码规范，将2个连续的字符计为单个字符
            $i += 2;
            $n++;
        } else {
            $i += 1;
            $n += 0.5;
        }
    }
    return $n;
}

// 判断是否在子网
function network_match($ip, $network)
{
    if (strpos($network, '/') > 0) {
        $network = explode('/', $network);
        $move = 32 - $network[1];
        if ($network[1] == 0) {
            return true;
        }
        return ((ip2long($ip) >> $move) === (ip2long($network[0]) >> $move)) ? true : false;
    } elseif ($network == $ip) {
        return true;
    } else {
        return false;
    }
}

// 递归替换
function preg_replace_r($search, $replace, $subject)
{
    while (preg_match($search, $subject)) {
        $subject = preg_replace($search, $replace, $subject);
    }
    return $subject;
}

// 获取字符串型自动编码
function get_auto_code($string, $start = '1')
{
    if (! $string) {
        return $start;
    }
    if (is_numeric($string)) { // 如果纯数字则直接加1
        return sprintf('%0' . strlen($string) . 's', $string + 1);
    } else { // 非纯数字则先分拆
        $reg = '/^([a-zA-Z-_]+)([0-9]+)$/';
        $str = preg_replace($reg, '$1', $string); // 字母部分
        $num = preg_replace($reg, '$2', $string); // 数字部分
        return $str . sprintf('%0' . (strlen($string) - strlen($str)) . 's', $num + 1);
    }
}

/**
 * 获取后台语言
 * @return string
 */
function get_backend_lang(): string
{
    $lg = cookie('b_lg');
    if ($acode = request()->param('acode')) {
        $lg = $acode;
        cookie('b_lg', $acode);
    }
    return $lg ?: 'cn';
}

/**
 * 获取前台语言
 * @return string
 */
function get_frontend_lang(): string
{
    $lg = cookie('f_lg');
    if (!$lg) {
        /* 去数据库中取默认的语言 */
        $lg = get_default_lang();
        $lg ?: $lg = 'cn';
        cookie('f_lg', $lg);
    }
    return $lg;
}

/**
 * 获取默认语言
 * @return string
 */
function get_default_lang(): string
{
    $acode = Db::name('cms_area')->cache('cms_default_lang')->where('is_default', 1)->value('acode');
    return $acode ?: 'cn';
}

/**
 * 设置后台语言
 * @param string $lg
 * @return void
 */
function set_backend_lang(string $lg): void
{
    cookie('b_lg', $lg);
}

/**
 * 设置前台语言
 * @param string $lg
 * @return void
 */
function set_forntend_lang(string $lg): void
{
    cookie('f_lg', $lg);
}

/**
 * 生成url
 * @param mixed $type               模型类型：1-单页，2-列表
 * @param mixed $urlname            模型urlname
 * @param mixed $pagetype           页面类型：list-列表，content-内容
 * @param mixed $scode              分类scode
 * @param mixed $sortfilename       分类定义的urlname
 * @param mixed $id                 内容id
 * @param mixed $contentfilename    内容定义的urlname
 * @return string|think\route\Url
 */
function bdurl($type, $urlname, $pagetype, $scode, $sortfilename, $id = '', $contentfilename = '')
{
    $url_break_char = '_';
    $url_rule_content_path = false;
    /* url模型 0去掉后缀 1携带.html后缀 */
    $url_type=get_sys_config('url_type')==1 ?true:false;

    if ($type == 1 || $pagetype == 'about') {
        $urlname = $urlname ?: 'about';
        if ($sortfilename) {
            $link = url('/'.$sortfilename,[],$url_type);
        } else {
            $link = url('/'.$urlname . $url_break_char . $scode,[],$url_type);
        }
    } else {
        $urlname = $urlname ?: 'list';
        if ($pagetype == 'list') {
            if ($sortfilename) {
                $link = url('/'.$sortfilename,[],$url_type);
            } else {
                $link = url('/'.$urlname . $url_break_char . $scode,[],$url_type);
            }
        } elseif ($pagetype == 'content') {
            if ($url_rule_content_path) {
                if ($contentfilename) {
                    $link = url('/'.$contentfilename, [], $url_type);
                } else {
                    $link = url('/'.$id, [], $url_type);
                }
            } else {
                if ($sortfilename && $contentfilename) {
                    $link = url('/'.$sortfilename . '/' . $contentfilename, [], $url_type);
                } elseif ($sortfilename) {
                    $link = url('/'.$sortfilename . '/' . $id, [], $url_type);
                } elseif ($contentfilename) {
                    $link = url('/'.$urlname . $url_break_char . $scode . '/' . $contentfilename, [], $url_type);
                } else {
                    $link = url('/'.$urlname . $url_break_char . $scode . '/' . $id, [], $url_type);
                }
            }
        } else {
            $link = 'javascript:;';
        }
    }
    $link = preg_replace("/\/((?!index)[\w]+)\.php\//i", "/", (string)$link);
    return $link;
}

/**
 * 缩放图片
 *
 * @param string $src_image  源图片路径
 * @param int $max_width     最大宽
 * @param int $max_height    最大高
 * @param int $img_quality   图片质量
 * @return string            返回缩放后的图片路径
 */
function resize_img(string|null $src_image, int $max_width = 0, int $max_height = 0, int $img_quality = 90): string
{
    if (!$src_image) {
        return '';
    }
    /* 原图片物理路径 */
    $src_image_path = public_path() . $src_image;

    $out_file = dirname($src_image).DIRECTORY_SEPARATOR.'mw' . $max_width . '_mh' . $max_height . '_' . basename($src_image);
    /* 输出物理路径 */
    $max_out_file = public_path() . $out_file;

    // 读取配置文件设置
    if (! $max_width) {
        $max_width = get_sys_config('ico_max_width') ?: 999999999;
    }
    if (! $max_height) {
        $max_height = get_sys_config('ico_max_height') ?: 999999999;
    }

    // 获取图片属性
    list($width, $height, $type, $attr) = getimagesize($src_image_path);

    // 创建目录
    if (!is_dir(dirname($max_out_file))) {
        Filesystem::mkdir(dirname($max_out_file));
    }

    // 无需缩放的图片直接拷贝
    if ($width <= $max_width && $height <= $max_height) {
        if ($src_image != $max_out_file) { // 存储地址不一致时进行拷贝
            if (! copy($src_image_path, $max_out_file)) {
                return $src_image;
            }
        }
        return $src_image;
    }

    // 求缩放比例
    if ($max_width && $max_height) {
        $scale = min($max_width / $width, $max_height / $height);
    } elseif ($max_width) {
        $scale = $max_width / $width;
    } elseif ($max_height) {
        $scale = $max_height / $height;
    }

    if ($scale < 1) {
        switch ($type) {
            case 1:
                $img = imagecreatefromgif(filename: $src_image_path);
                break;
            case 2:
                $img = imagecreatefromjpeg($src_image_path);
                break;
            case 3:
                $img = imagecreatefrompng($src_image_path);
                break;
        }

        $new_width = floor($scale * $width);
        $new_height = floor($scale * $height);
        $new_img = imagecreatetruecolor($new_width, $new_height); // 创建画布

        // 创建透明画布,避免黑色
        if ($type == 1 || $type == 3) {
            $color = imagecolorallocate($new_img, 255, 255, 255);
            imagefill($new_img, 0, 0, $color);
            imagecolortransparent($new_img, $color);
        }
        imagecopyresized($new_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        switch ($type) {
            case 1:
                imagegif($new_img, $max_out_file);
                break;
            case 2:
                imagejpeg($new_img, $max_out_file, $img_quality);
                break;
            case 3:
                imagepng($new_img, $max_out_file, $img_quality / 10); // $quality参数取值范围0-99 在php 5.1.2之后变更为0-9
                break;
            default:
                imagejpeg($new_img, $max_out_file, $img_quality);
        }
        imagedestroy($new_img);
        imagedestroy($img);
    }
    return $out_file;
}


// 检测目录是否存在
function check_dir($path, $create = false)
{
    if (is_dir($path)) {
        return true;
    } elseif ($create) {
        return create_dir($path);
    }
}

// 创建目录
function create_dir($path)
{
    if (! file_exists($path)) {
        if (mkdir($path, 0777, true)) {
            return true;
        }
    }
    return false;
}

// 检查文件是否存在
function check_file($path, $create = false, $content = null)
{
    if (file_exists($path)) {
        return true;
    } elseif ($create) {
        return create_file($path, $content);
    }
}

// 创建文件
function create_file($path, $content = null, $over = false)
{
    if (file_exists($path) && ! $over) {
        return false;
    } elseif (file_exists($path)) {
        @unlink($path);
    }
    check_dir(dirname($path), true);
    $handle = fopen($path, 'w') or throw new \Exception('创建文件失败，请检查目录权限！');
    fwrite($handle, $content ?? '');
    return fclose($handle);
}

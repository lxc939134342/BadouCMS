<?php

namespace app\common\controller;

use think\Response;
use app\BaseController;
use think\facade\Event;
use think\facade\Config;
use think\facade\Cookie;
use think\exception\HttpResponseException;
use app\common\library\FrontendAuth as Auth;

/**
 * 前台控制器基类
 */
class Api extends BaseController
{
    /**
     * 默认响应输出类型,支持json/xml/jsonp
     * @var string
     */
    protected $responseType = 'json';

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 权限Auth
     * @var Auth
     */
    protected $auth = null;

    public function initialize()
    {
        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');
        $modulename     = app('http')->getName();
        $controllername = strtolower($this->request->controller());
        $actionname = strtolower($this->request->action());

        // 检测IP是否允许
        check_ip_allowed();

        $this->auth = Auth::instance();

        // token
        $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', Cookie::get('token', '')));

        $path = str_replace('.', '/', $controllername) . '/' . $actionname;
        // 设置当前请求的URI
        $this->auth->setRequestUri($path);
        // 检测是否需要验证登录
        if (!$this->auth->match($this->noNeedLogin)) {
            //初始化
            $this->auth->init($token);
            //检测是否登录
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'));
            }
            // 判断是否需要验证权限
            if (!$this->auth->match($this->noNeedRight)) {
                // 判断控制器和方法判断是否有对应权限
                if (!$this->auth->check($path)) {
                    $this->error(__('You have no permission'));
                }
            }
        } else {
            // 如果有传递token才验证是否登录状态
            if ($token) {
                $this->auth->init($token);
            }
        }

        // 语言检测
        $lang = $this->app->lang->getLangSet();
        $lang = preg_match("/^([a-zA-Z\-_]{2,10})\$/i", $lang) ? $lang : 'zh-cn';

        $site = get_sys_config();

        $upload = \app\common\model\Config::upload();

        // 上传信息配置后
        Event::trigger("upload_config_init", $upload);

        // 配置信息
        $config = [
            'site'           => array_intersect_key($site, array_flip(['site_name', 'version'])),
            'upload'         => $upload,
            'modulename'     => $modulename,
            'controllername' => $controllername,
            'actionname'     => $actionname,
            'jsname'         => 'frontend/' . str_replace('.', '/', $controllername),
            'moduleurl'      => rtrim(url("/{$modulename}", [], false), '/'),
            'language'       => $lang,
            'app_url'        => $this->request->root(true),
        ];
        $config = array_merge($config, Config::get("view_replace_str"));

        Config::set(array_merge(Config::get('upload'), $upload), 'upload');

        // 配置信息后
        Event::trigger("config_init", $config);
        // 加载当前控制器语言包
        $this->loadlang($controllername);
    }

    /**
     * 加载语言文件
     * @param string $name
     */
    protected function loadlang($name, $lang = null)
    {
        $name    = preg_match("/^([a-zA-Z0-9_\.\/]+)\$/i", $name) ? $name : 'index';
        if (!$lang) {
            $lang    = $this->app->lang->getLangSet();
        }

        $lang    = preg_match("/^([a-zA-Z\-_]{2,10})\$/i", $lang) ? $lang : 'zh-cn';

        $langArr = $this->app->lang->load([
            app_path() . 'lang' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . (str_replace('.', DIRECTORY_SEPARATOR, $name)) . '.php',
        ]);
        return $langArr;
    }

    /**
     * 刷新Token
     */
    protected function token()
    {
        $check = $this->request->checkToken('__token__');
        // 刷新token
        $token = $this->request->buildToken();
        if ($this->request->isAjax()) {
            header('__token__: ' . $token);
        }
        if (false === $check) {
            $this->error('令牌错误！', ['__token__' => $token]);
        }
    }

    /**
    * 操作成功
    * @param string      $msg     提示消息
    * @param mixed       $data    返回数据
    * @param int         $code    错误码
    * @param string|null $type    输出类型
    * @param array       $header  发送的 header 信息
    * @param array       $options Response 输出参数
    */
    protected function success(string $msg = '', mixed $data = null, int $code = 1, ?string $type = null, array $header = [], array $options = []): void
    {
        $this->result($msg, $data, $code, $type, $header, $options);
    }

    /**
     * 操作失败
     * @param string      $msg     提示消息
     * @param mixed       $data    返回数据
     * @param int         $code    错误码
     * @param string|null $type    输出类型
     * @param array       $header  发送的 header 信息
     * @param array       $options Response 输出参数
     */
    protected function error(string $msg = '', mixed $data = null, int $code = 0, ?string $type = null, array $header = [], array $options = []): void
    {
        $this->result($msg, $data, $code, $type, $header, $options);
    }

    /**
     * 返回 API 数据
     * @param string      $msg     提示消息
     * @param mixed       $data    返回数据
     * @param int         $code    错误码
     * @param string|null $type    输出类型
     * @param array       $header  发送的 header 信息
     * @param array       $options Response 输出参数
     */
    public function result(string $msg, mixed $data = null, int $code = 0, ?string $type = null, array $header = [], array $options = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => $this->request->server('REQUEST_TIME'),
            'data' => $data,
        ];

        $type = $type ?: $this->responseType;
        $code = $header['statusCode'] ?? 200;

        $response = Response::create($result, $type, $code)->header($header)->options($options);
        throw new HttpResponseException($response);
    }
}

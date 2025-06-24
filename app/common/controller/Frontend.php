<?php

namespace app\common\controller;

use app\BaseController;
use think\facade\View;
use think\facade\Event;
use think\facade\Config;
use think\facade\Cookie;
use app\common\traits\Jump;
use app\common\library\FrontendAuth as Auth;
use app\common\traits\View as ViewTrait;

/**
 * 前台控制器基类
 */
class Frontend extends BaseController
{
    //跳转
    use Jump;
    //引入视图方法
    use ViewTrait;
    /**
     * @var \think\View 视图类实例
     */
    protected $view;

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
        $this->view     = View::instance();
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
                $this->error(__('Please login first'), 'index/user/login');
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

        $this->view->assign('user', $this->auth->getUser());

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
        $this->assign('site', $site);
        $this->assign('config', $config);
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
     * 渲染配置信息
     * @param mixed $name  键名或数组
     * @param mixed $value 值
     */
    protected function assignconfig($name, $value = '')
    {
        $this->view->config = array_merge($this->view->config ? $this->view->config : [], is_array($name) ? $name : [$name => $value]);
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
            $this->error('令牌错误！', '', ['__token__' => $token]);
        }
    }

    /**
     * 判断是否为ajax请求
     */
    protected function isAjax()
    {
        if ($this->request->param('view', 0) ||
            (!$this->request->isAjax() && !$this->request->isJson())) {
            return false;
        }
        return true;
    }
}

<?php

namespace app\common\controller;

use app\BaseController;
use app\common\library\AdminAuth;
use app\common\traits\Jump;
use app\common\traits\View as ViewTrait;
use badou\Auth;
use think\App;
use think\facade\Config;
use think\facade\Event;
use think\facade\Session;
use think\facade\View;

class Backend extends BaseController
{
    //引入视图方法
    use ViewTrait;

    //跳转
    use Jump;

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
     * 布局模板
     * @var string
     */
    protected $layout = 'default';

    /**
     * 权限控制类
     * @var Auth
     */
    protected $auth = null;

    /**
     * 模型对象
     * @var \think\Model
     */
    protected $model = null;

    /**
     * 快速搜索时执行查找的字段
     */
    protected $searchFields = 'id';

    /**
     * 是否是关联查询
     */
    protected $relationSearch = false;

    /**
     * 是否开启数据限制
     * 支持auth/personal
     * 表示按权限判断/仅限个人
     * 默认为禁用,若启用请务必保证表中存在admin_id字段
     */
    protected $dataLimit = false;

    /**
     * 数据限制字段
     */
    protected $dataLimitField = 'admin_id';

    /**
     * 数据限制开启时自动填充限制字段值
     */
    protected $dataLimitFieldAutoFill = true;

    /**
     * 是否开启Validate验证
     */
    protected $modelValidate = false;

    /**
     * 是否开启模型场景验证
     */
    protected $modelSceneValidate = false;

    /**
     * Multi方法可批量修改的字段
     */
    protected $multiFields = 'status';

    /**
     * Selectpage可显示的字段
     */
    protected $selectpageFields = '*';

    /**
     * 前台提交过来,需要排除的字段数据
     */
    protected $excludeFields = "";

    /**
     * 导入文件首行类型
     * 支持comment/name
     * 表示注释或字段名
     */
    protected $importHeadType = 'comment';

    public function initialize()
    {
        parent::initialize();
        $this->view     = View::instance();
        $modulename     = app('http')->getName();
        $controllername = strtolower($this->request->controller());
        $actionname     = strtolower($this->request->action());

        $path = str_replace('.', '/', $controllername) . '/' . $actionname;

        // 检测IP是否允许
        check_ip_allowed();

        $this->auth = AdminAuth::instance();

        // 设置当前请求的URI
        $this->auth->setRequestUri($path);
        // 检测是否需要验证登录
        if (!$this->auth->match($this->noNeedLogin)) {
            //检测是否登录
            if (!$this->auth->isLogin()) {
                Event::trigger('admin_nologin', $this);
//                Hook::listen('admin_nologin', $this);
                $url = Session::get('refererd');
                $url = $url ? $url : $this->request->url();
                if (in_array($this->request->pathinfo(), ['/', 'index/index'])) {
                    redirect('index/login', 302);
                    exit;
                }
                $this->error(__('Please login first'), url('index/login', ['url' => $url]));
            }
            // 判断是否需要验证权限
            if (!$this->auth->match($this->noNeedRight)) {
                // 判断控制器和方法是否有对应权限
                if (!$this->auth->check($path)) {
                    Event::trigger('admin_nopermission', $this);
                    $this->error(__('You have no permission'), '');
                }
            }
        }

//        // 如果有使用模板布局
//        if ($this->layout) {
//            $this->view->engine->layout('layout/' . $this->layout);
//        }

        // 语言检测
        $lang = $this->app->lang->getLangSet();
        $lang = preg_match("/^([a-zA-Z\-_]{2,10})\$/i", $lang) ? $lang : 'zh-cn';

        $site = Config::get("site");

        $upload = \app\common\model\Config::upload();

        // 上传信息配置后
        Event::trigger('upload_config_init', $upload);

        // 配置信息
        $config = [
            'site'           => array_intersect_key($site, array_flip(['name', 'indexurl', 'cdnurl', 'version', 'timezone', 'languages'])),
            'upload'         => $upload,
            'modulename'     => $modulename,
            'controllername' => $controllername,
            'actionname'     => $actionname,
            'jsname'         => 'backend/' . str_replace('.', '/', $controllername),
            'moduleurl'      => rtrim(url("/{$modulename}", [], false), '/'),
            'language'       => $lang,
            'referer'        => Session::get("referer"),
            'app_url'        => $this->request->root(true),
        ];
        $config = array_merge($config, Config::get("view_replace_str"));

        Config::set(array_merge(Config::get('upload'), $upload), 'upload');

        // 配置信息后
        Event::trigger('config_init', $config);
        //渲染站点配置
        $this->assign('site', $site);
        //渲染配置信息
        $this->assign('config', $config);
        //渲染权限对象
        $this->assign('auth', $this->auth);
        //渲染管理员对象
        $this->assign('admin', Session::get('admin'));

        //加载当前控制器语言包
        $this->loadlang($controllername, $lang);
    }

    //加载语言包
    protected function loadlang($name, $lang)
    {
        $name    = preg_match("/^([a-zA-Z0-9_\.\/]+)\$/i", $name) ? $name : 'index';
        $lang    = preg_match("/^([a-zA-Z\-_]{2,10})\$/i", $lang) ? $lang : 'zh-cn';
        $langArr = $this->app->lang->load([
            app_path() . 'lang' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . (str_replace('.', DIRECTORY_SEPARATOR, $name)) . '.php',
        ]);
        $this->assignconfig('lang', $langArr);
    }
}
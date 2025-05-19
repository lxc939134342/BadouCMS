<?php

namespace app\common\controller;

use badou\Tree;
use think\Model;
use think\facade\View;
use app\BaseController;
use think\facade\Event;
use think\facade\Config;
use think\facade\Session;
use app\common\traits\Jump;
use app\common\library\AdminAuth;
use app\common\traits\View as ViewTrait;

class Backend extends BaseController
{
    //引入视图方法
    use ViewTrait;
    //跳转
    use Jump;
    // 引入后台控制器的通用方法
    use \app\common\traits\Backend;

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
     * 权限控制类
     * @var AdminAuth
     */
    protected $auth = null;

    /**
     * 模型对象
     * @var Model
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
                $url = Session::get('refererd');
                $url = $url ? $url : $this->request->url();
                if (in_array($this->request->pathinfo(), ['','/', 'index/index'])) {
                    $this->redirect((string)url('index/login', ['referer' => $url]), 302);
                }
                $this->error(__('Please login first'), (string)url('index/login', ['url' => $url]));
            }
            // 判断是否需要验证权限
            if (!$this->auth->match($this->noNeedRight)) {
                // 判断控制器和方法是否有对应权限
                if (!$this->auth->check($path)) {
                    Event::trigger('admin_nopermission', $this);
                    $this->error(__('You have no permission'));
                }
            }
        }

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
            'moduleurl'      => rtrim(url("/", [], false), '/'),
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
        return $langArr;
    }

    /**
     * 判断是否为ajax请求 一定要用此方法判断
     * layui加载页面使用的是ajax形式，会导致request->ajax不精准
     */
    protected function isAjax()
    {
        if ($this->request->param('view', 0) ||
            (!$this->request->isAjax() && !$this->request->isJson())) {
            return false;
        }
        return true;
    }

    /**
     * 获取数据限制的管理员ID
     * 禁用数据限制时返回的是null
     * @return mixed
     */
    protected function getDataLimitAdminIds()
    {
        if (!$this->dataLimit) {
            return null;
        }
        if ($this->auth->isSuperAdmin()) {
            return null;
        }
        $adminIds = [];
        if (in_array($this->dataLimit, ['auth', 'personal'])) {
            $adminIds = $this->dataLimit == 'auth' ? $this->auth->getChildrenAdminIds(true) : [$this->auth->id];
        }
        return $adminIds;
    }

    /**
     * Selectpage的实现方法
     *
     * 当前方法只是一个比较通用的搜索匹配,请按需重载此方法来编写自己的搜索逻辑,$where按自己的需求写即可
     * 这里示例了所有的参数，所以比较复杂，实现上自己实现只需简单的几行即可
     *
     */
    protected function selectpage()
    {
        //设置过滤方法
        $this->request->filter(['trim', 'strip_tags', 'htmlspecialchars']);

        //搜索关键词,客户端输入以空格分开,这里接收为数组
        $word = (array)$this->request->request("q_word/a");
        //当前页
        $page = $this->request->request("pageNumber", 1);
        //分页大小
        $pagesize = $this->request->request("pageSize", 10);
        //搜索条件
        $andor = $this->request->request("andOr", "and", "strtoupper");
        //排序方式
        $orderby = (array)$this->request->request("orderBy/a");
        //显示的字段
        $field = $this->request->request("showField");
        //主键
        $primarykey = $this->request->request("keyField");
        //主键值
        $primaryvalue = $this->request->request("keyValue");
        //搜索字段
        $searchfield = (array)$this->request->request("searchField/a");
        //自定义搜索条件
        $custom = (array)$this->request->request("custom/a");
        //是否返回树形结构
        $istree = $this->request->request("isTree", 0);
        $ishtml = $this->request->request("isHtml", 0);
        if ($istree) {
            $word = [];
            $pagesize = 999999;
        }
        $order = [];
        foreach ($orderby as $k => $v) {
            $order[$v[0]] = $v[1];
        }
        $field = $field ? $field : 'name';

        //如果有primaryvalue,说明当前是初始化传值
        if ($primaryvalue !== null) {
            $where = [$primarykey => ['in', $primaryvalue]];
            $pagesize = 999999;
        } else {
            $where = function ($query) use ($word, $andor, $field, $searchfield, $custom) {
                $logic = $andor == 'AND' ? '&' : '|';
                $searchfield = is_array($searchfield) ? implode($logic, $searchfield) : $searchfield;
                $searchfield = str_replace(',', $logic, $searchfield);
                $word = array_filter(array_unique($word));
                if (count($word) == 1) {
                    $query->where($searchfield, "like", "%" . reset($word) . "%");
                } else {
                    $query->where(function ($query) use ($word, $searchfield) {
                        foreach ($word as $index => $item) {
                            $query->whereOr(function ($query) use ($item, $searchfield) {
                                $query->where($searchfield, "like", "%{$item}%");
                            });
                        }
                    });
                }
                if ($custom && is_array($custom)) {
                    foreach ($custom as $k => $v) {
                        if (is_array($v) && 2 == count($v)) {
                            $query->where($k, trim($v[0]), $v[1]);
                        } else {
                            $query->where($k, '=', $v);
                        }
                    }
                }
            };
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = [];
        $total = $this->model->where($where)->count();
        if ($total > 0) {
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }

            $fields = is_array($this->selectpageFields) ? $this->selectpageFields : ($this->selectpageFields && $this->selectpageFields != '*' ? explode(',', $this->selectpageFields) : []);

            //如果有primaryvalue,说明当前是初始化传值,按照选择顺序排序
            if ($primaryvalue !== null && preg_match("/^[a-z0-9_\-]+$/i", $primarykey)) {
                $primaryvalue = array_unique(is_array($primaryvalue) ? $primaryvalue : explode(',', $primaryvalue));
                //修复自定义data-primary-key为字符串内容时，给排序字段添加上引号
                $primaryvalue = array_map(function ($value) {
                    return '\'' . $value . '\'';
                }, $primaryvalue);

                $primaryvalue = implode(',', $primaryvalue);

                $this->model->orderRaw("FIELD(`{$primarykey}`, {$primaryvalue})");
            } else {
                $this->model->order($order);
            }

            $datalist = $this->model->where($where)
                ->page($page, $pagesize)
                ->select();

            foreach ($datalist as $index => $item) {
                unset($item['password']);
                if ($this->selectpageFields == '*') {
                    $result = [
                        $primarykey => $item[$primarykey] ?? '',
                        $field      => $item[$field] ?? '',
                    ];
                } else {
                    $result = array_intersect_key(($item instanceof Model ? $item->toArray() : (array)$item), array_flip($fields));
                }
                $result['pid'] = isset($item['pid']) ? $item['pid'] : (isset($item['parent_id']) ? $item['parent_id'] : 0);
                $list[] = $result;
            }
            if ($istree && !$primaryvalue) {
                $tree = Tree::instance();

                $tree->init($list, 'pid');
                $list = $tree->getTreeList($tree->getTreeArray(0), $field);
                if (!$ishtml) {
                    foreach ($list as &$item) {
                        $item = str_replace('&nbsp;', ' ', $item);
                    }
                    unset($item);
                }
            }
        }
        //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
        $this->success('ok', '', ['list' => $list, 'total' => $total]);
    }


    /**
     * 通用的添加方法
     *
     * @param array $data 提交的数据
     * @return array
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


}

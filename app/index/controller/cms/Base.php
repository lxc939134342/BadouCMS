<?php

// +----------------------------------------------------------------------
// | BADOUCMS [ 八斗网站系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2024-2030 http://doc.ldcode.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: lande <939134342@qq.com>
// +----------------------------------------------------------------------

namespace app\index\controller\cms;

use bd\Jump;
use ba\Random;
use Throwable;
use bd\taglib\Bd;
use think\facade\Db;
use think\facade\View;
use app\BaseController;
use think\facade\Event;
use think\facade\Config;
use think\facade\Cookie;
use app\common\library\Auth;
use app\index\model\cms\Site;
use app\common\library\Upload;
use app\index\model\cms\Label;
use app\index\model\cms\Company;
use app\index\model\cms\Content;
use app\index\model\cms\ContentSort;
use think\db\exception\PDOException;
use think\exception\HttpResponseException;
use app\common\library\token\TokenExpirationException;
use app\index\model\cms\User;

class Base extends BaseController
{
    use Jump;

    /**
     * 无需登录的方法
     * 访问本控制器的此方法，无需会员登录
     * @var array
     */
    protected array $noNeedLogin = ['captcha'];

    /**
     * 无需鉴权的方法
     * @var array
     */
    protected array $noNeedPermission = [];

    /**
     * 应用站点系统设置
     * @var bool
     */
    protected bool $useSystemSettings = true;

    /**
     * 视图实例
     *
     * @var \think\View
     */
    protected object $view;

    /**
     * 权限类实例
     * @var Auth
     */
    protected Auth $auth;

    /**
     * 站点配置
     * @var array
     */
    protected array $site;

    /**
     * 标签信息
     * @var array
     */
    protected array $label;

    /**
    * 公司信息
    * @var array
    */
    protected array $company;

    /**
     * 分类信息
     * @var object
     */
    protected $contentSort;

    /**
     * 内容信息
     * @var
     */
    protected $contentInfo;

    /**
     * 当前操作路径
     * @var string
     */
    protected string $actionPath;

    /**
     * 初始化
     * @throws Throwable
     * @throws HttpResponseException
     */
    public function initialize(): void
    {
        // 系统站点配置
        if ($this->useSystemSettings) {
            // 检查数据库连接
            try {
                Db::execute("SELECT 1");
            } catch (PDOException $e) {
                $this->error(mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8,GBK,GB2312,BIG5'));
            }

            ip_check(); // ip检查
            set_timezone(); // 时区设定
        }
        parent::initialize();

        $controllername = strtolower($this->request->controller());
        $actionname = strtolower($this->request->action());

        $this->site = (new Site())->getSiteData();
        $this->company = (new Company())->getCompanyData();
        $this->label = (new Label())->getLabelData();

        $theme = isset($this->site['theme']) && !empty($this->site['theme']) ? $this->site['theme'] : 'default';

        // 手机端模版
        // TODO 待完善
        /* 设置模版路径 */
        $view_path = root_path() . 'template' . DIRECTORY_SEPARATOR. $theme. DIRECTORY_SEPARATOR;
        if (!is_dir($view_path)) {  //兼容public目录下的模版
            $tpl_html_dir = get_sys_config('tpl_html_dir') ?: 'html';
            $view_path = public_path() . 'template' . DIRECTORY_SEPARATOR. $theme. DIRECTORY_SEPARATOR.$tpl_html_dir. DIRECTORY_SEPARATOR;
        }
        $this->view = View::instance();
        $this->view->config([
            'view_path' => $view_path,
            'taglib_pre_load' => Bd::class
        ]);

        /**
         * 设置默认过滤规则
         * @see filter()
         */
        $this->request->filter('filter');

        // 加载控制器语言包
        $lang = get_frontend_lang();
        if ($lang == 'cn') {
            $lang = 'zh-cn';
        }
        $this->app->lang->setLangSet($lang);
        $langSet = $this->app->lang->getLangSet();
        $this->app->lang->load([
            app_path() . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . (str_replace('/', DIRECTORY_SEPARATOR, $this->app->request->controllerPath)) . '.php',
            app_path() . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR.'cms'.DIRECTORY_SEPARATOR.'index.php'
        ]);

        $needLogin = !action_in_arr($this->noNeedLogin);
        try {
            // 初始化会员鉴权实例
            $this->auth = Auth::instance();
            $token      = get_auth_token(['ba', 'user', 'token']);
            if (!$token) {
                $token = Cookie::get('token');
            }
            if ($token) {
                Cookie::set('token', $token);
                $this->auth->init($token);
            }
        } catch (TokenExpirationException) {
            if ($needLogin) {
                $this->error(__('Token expiration'));
            }
        }
        $routePath = ($this->app->request->controllerPath ?? '') . '/' . $this->request->action(true);
        $this->actionPath = str_replace('cms', '', $routePath);
        if ($needLogin) {
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'), '/user/login');
            }
            if (!action_in_arr($this->noNeedPermission)) {
                if (!$this->auth->check($routePath)) {
                    $this->error(__('You have no permission'));
                }
            }
        }

        // 配置信息
        $config = [
            'uuid' => Random::uuid(),
            'controllername' => $controllername,
            'actionname'     => $actionname,
            'actionPath'     => $this->actionPath,
            'language' => $langSet,
            'siteConfig' => [
                'siteName'     => get_sys_config('site_name'),
                'recordNumber' => get_sys_config('record_number'),
                'version'      => get_sys_config('version'),
                'cdnUrl'       => full_url(),
                'uploadUrl'    => '/upload',
                'upload'       => keys_to_camel_case(get_upload_config(), ['max_size', 'save_name', 'allowed_suffixes', 'allowed_mime_types']),
            ],
            'captchaUrl' => '/api/cms.common/captcha',
        ];

        $user = new User();
        $userInfo = $user->getUserInfo($this->auth->id);
        $this->view->assign('user', $userInfo);
        $this->view->assign('config', $config);
        $this->assignBd();
        $this->assignconfig('user', $userInfo);
        $this->assignconfig('captchaUrl', $config['captchaUrl']);
        // 会员验权和登录标签位
        Event::trigger('cmsInit', $this->auth);
        $this->getSort();

    }

    /*获取分类信息*/
    protected function getSort()
    {
        $contentSortModel = new ContentSort();
        $urlname = $this->request->param('category');
        if (!empty($urlname)) {
            /* 获取当前栏目信息 */
            $regex = '/^(\w+)_(\d+)$/';
            if (preg_match($regex, $urlname, $match)) {
                $urlname = $match[2];
            }

            $this->contentSort = $contentSortModel->getSort($urlname);
            /*内容不存在*/
            if (!$this->contentSort) {
                abort(404, __('Not found'));
            }
            $tcode = $contentSortModel->getSortTopScode($this->contentSort['scode']);
            $this->contentSort['tcode'] = $tcode;
            $this->contentSort['toprows'] = $contentSortModel->getSortRows(0);
            // 获取顶级栏目
            if ($tcode) {
                $top_sort = $contentSortModel->getSort($tcode);
                $this->contentSort['topname'] = $top_sort['name'] ?? '';
                $this->contentSort['toplink'] = $top_sort['link'] ?? '';
                $this->contentSort['toprows'] = $contentSortModel->getSortRows($tcode);
            }

            // 获取父级栏目
            if ($this->contentSort['pcode']) {
                $parent_sort = $contentSortModel->getSort($this->contentSort['pcode']);
            } else {
                $parent_sort = $top_sort;
            }

            $this->contentSort['parentname'] = $parent_sort['name'] ?? '';
            $this->contentSort['parentlink'] = $parent_sort['link'] ?? '';
            $this->contentSort['parentrows'] = $contentSortModel->getSortRows($parent_sort['scode']);

            $this->view->assign('sort', $this->contentSort);
            $this->view->assign('listsort', $this->contentSort);
            /*验证权限*/
            if ($this->contentSort['gid']) {
                $this->checkPageLevel($this->contentSort['gid'], $this->contentSort['gtype']);
            }

            /*如果是单页直接获取内容*/
            if ($this->contentSort['type'] == 1) {
                $contentModel = new Content();
                $this->contentInfo = $contentModel::getContent($this->contentSort['scode']);
                if (empty($this->contentInfo)) {
                    /*内容不存在*/
                    abort(404, __("Not found"));
                }

                $this->view->assign('content', $this->contentInfo);
            }
        } else {
            $this->contentSort = $contentSortModel->getDefaultData();
            $this->contentSort['toprows'] = $contentSortModel->getSortRows(0);
            $this->contentSort['parentrows'] = $this->contentSort['toprows'];
            $this->view->assign('sort', $this->contentSort);
            $this->view->assign('listsort', $this->contentSort);
        }
    }

    /**
     * 渲染标签信息
     */
    protected function assignBd(): void
    {
        $bdassign = [
            'sitepath' => request()->domain(true),
            'scaction' => url('/search'),
            'sitemap' => url('/sitemap', [], 'xml'),
            'msgaction' => url('/message'),
            'sitetplpath' => request()->domain().'/template/'. $this->site['theme'],
            'checkcode' => $this->view->config['captchaUrl'],
            'islogin' => $this->auth->isLogin(),
            'registerstatus' => true,
            'loginstatus' => true,
            'register' => url('/user/register'),
            'login' => url('/user/login'),
            'lgpath' => url('/do/area'),
            'sitelanguage' => get_frontend_lang(),
            'httpurl' => request()->domain(),
            'pageurl' => request()->url(true),
            'commentstatus' => get_sys_config('commentstatus'),
            'commentaction' => url('/comment/add'),
            'commentcodestatus' => 1,
            'msgcodestatus' => get_sys_config('msgcodestatus'),
            'pagetitle' => $this->site['sitetitle'],
            'pagedescription' => $this->site['sitedescription'],
            'pagekeywords' => $this->site['sitekeywords'],
        ];
        $api_data = $this->apiSecret();
        $this->view->assign('bd', array_merge($bdassign, $api_data, $this->site, $this->company, $this->label));
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

    public function upload(): void
    {
        $file   = $this->request->file('file');
        $driver = $this->request->param('driver', 'local');
        $topic  = $this->request->param('topic', 'default');
        try {
            $upload     = new Upload();
            $attachment = $upload
                ->setFile($file)
                ->setDriver($driver)
                ->setTopic($topic)
                ->upload(null, 0, $this->auth->id);
            unset($attachment['create_time'], $attachment['quote']);
        } catch (Throwable $e) {
            $this->result([], 0, $e->getMessage(), 'json');
        }
        $this->result([
            'file' => $attachment ?? []
        ], 1, 'ok', 'json');
    }

    /**
     * 检查页面权限
     * @param $gcode //权限编码
     * @param $gtype //权限类型
     * @param $gnote //错误提示
     * @return void
     */
    protected function checkPageLevel(int $gcode, string $gtype, string $gnote = ''): void
    {
        /*判断是否登录*/
        if (!$this->auth->isLogin()) {
            $this->redirect('/user/login');
        }

        if ($gcode) {
            $deny = false;
            $gtype = $gtype ?: 4;
            $userInfo = $this->auth->getUserInfo();
            switch ($gtype) {
                case 1:
                    if ($gcode <= $userInfo['level']) {
                        $deny = true;
                    }
                    break;
                case 2:
                    if ($gcode < $userInfo['level']) {
                        $deny = true;
                    }
                    break;
                case 3:
                    if ($gcode != $userInfo['level']) {
                        $deny = true;
                    }
                    break;
                case 4:
                    if ($gcode > $userInfo['level']) {
                        $deny = true;
                    }
                    break;
                case 5:
                    if ($gcode >= $userInfo['level']) {
                        $deny = true;
                    }
                    break;
            }
            if ($deny) {
                $gnote = $gnote ?: 'Permission denied';
                $this->error(__($gnote));
            }
        }
    }

    /**
     * 用户初始化
     * @return void
     */
    public function userInitialize(): void
    {
        if ($this->auth->isLogin()) {
            $menus = [];
            $rules     = [];
            $userMenus = $this->auth->getMenus();

            // 首页加载的规则，验权，但过滤掉会员中心菜单
            foreach ($userMenus as $item) {
                if ($item['type'] == 'menu_dir') {
                    foreach ($item['children'] as &$child) {
                        $child['path'] = '/'.$child['path'];
                    }
                    unset($child);
                    $menus[] = $item;

                } elseif ($item['type'] != 'menu') {
                    $rules[] = $item;
                }
            }
            $rules = array_values($rules);
            if (!$this->contentSort['scode']) {
                $this->view->assign('sort', []);
            }
            $this->view->assign([
                'site'             => [
                    'siteName'     => get_sys_config('site_name'),
                    'recordNumber' => get_sys_config('record_number'),
                    'version'      => get_sys_config('version'),
                    'cdnUrl'       => full_url(),
                    'upload'       => keys_to_camel_case(get_upload_config(), ['max_size', 'save_name', 'allowed_suffixes', 'allowed_mime_types']),
                ],
                'openMemberCenter' => Config::get('buildadmin.open_member_center'),
                'rules'            => $rules,
                'menus'            => $menus,
            ]);
        }
    }

    /**
     * api授权
     * @return array
     */
    protected function apiSecret()
    {
        $timestamp = time();
        $signature = md5(md5(get_sys_config('api_appid').get_sys_config('api_secret').$timestamp));
        return [
            'appid' => get_sys_config('api_appid'),
            'timestamp' => $timestamp,
            'signature' => $signature,
        ];
    }

    /**
     * 跳转到对应的语言区域
     * @return void
     */
    public function area()
    {
        $lg = $this->request->param('lg');
        if (!$lg) {
            $this->redirect('/');
        }
        $cms_area = Db::name('cms_area')
            ->where('acode', $lg)
            ->field('domain,acode')->find();
        if (!$cms_area || empty($cms_area['domain'])) {
            $this->redirect('/');
        }

        $domain = $cms_area['domain'];
        // 使用简化的正则表达式只检查是否包含 http 或 https 前缀
        if (!preg_match('/^https?:\/\//i', $domain)) {
            $domain = $this->request->scheme() . '://' . $domain;
        }

        $this->redirect($domain);
    }
}

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

use Throwable;
use think\facade\Db;
use think\facade\View;
use think\facade\Event;
use think\facade\Config;
use app\index\model\cms\Site;
use app\index\model\cms\Label;
use app\index\model\cms\Company;
use app\index\model\cms\Content;
use app\common\controller\Frontend;
use app\index\model\cms\ContentSort;
use think\exception\HttpResponseException;
use modules\cms\taglib\Bd;

class Base extends Frontend
{
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
        $lang = get_frontend_lang() == 'cn' ? 'zh-cn' : get_frontend_lang();
        $this->app->lang->load([
            app_path() . 'lang' . DIRECTORY_SEPARATOR . $lang . '.php'
        ]);
        parent::initialize();
        $this->getSort();
        $this->site = (new Site())->getSiteData();
        $this->company = (new Company())->getCompanyData();
        $this->label = (new Label())->getLabelData();
        $this->setTheme();
        $this->assignBd();
        // 会员验权和登录标签位
        Event::trigger('cmsInit', $this->auth);
        $controllername = strtolower($this->request->controller());
        $this->loadlang('cms.index', get_frontend_lang());
        $this->loadlang($controllername, get_frontend_lang());

        // 初始化钩子：允许插件在此注入视图变量或执行初始化逻辑
        $this->triggerObserver('Init', $this);
    }

    /*获取分类信息*/
    protected function getSort()
    {
        $contentSortModel = new ContentSort();
        $urlname = $this->request->param('category');

        // BeforeGetSort 钩子：允许插件接管分类解析逻辑
        $hookRes = $this->triggerObserver('BeforeGetSort', $urlname);
        if ($hookRes === false) return; // 被插件拦截
        if (is_array($hookRes)) {
            $this->contentSort = $hookRes['contentSort'] ?? $this->contentSort;
            $this->contentInfo = $hookRes['contentInfo'] ?? $this->contentInfo;
            if (isset($hookRes['abort'])) abort($hookRes['abort'], $hookRes['msg'] ?? '');
            return;
        }

        if (!empty($urlname)) {
            /* 获取当前栏目信息 */
            $regex = '/^([\w\-]+)_(\d+)$/';
            if (preg_match($regex, $urlname, $match)) {
                $urlname = $match[2];
            }

            //如果id存在，则先获取内容再获取栏目信息，防止因为栏目名称相同而获取到别的语言导致内容不存在
            if ($id = $this->request->param('id')) {
                $contentModel = new Content();
                $this->contentInfo = $contentModel::getContent('', $id);
                if (!$this->contentInfo) {
                    abort(404, __('Not found'));
                }
                set_forntend_lang($this->contentInfo['acode']);
                $this->contentSort = $contentSortModel->getSort($this->contentInfo['scode']);
            } else {
                $this->contentSort = $contentSortModel->getSort($urlname);
                if (!$this->contentSort) {
                    $this->contentSort = $contentSortModel->getSortNotLang($urlname);
                }
            }
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
                $parent_sort = $top_sort ?? null;
            }

            $this->contentSort['parentname'] = $parent_sort['name'] ?? '';
            $this->contentSort['parentlink'] = $parent_sort['link'] ?? '';
            $this->contentSort['parentrows'] = $contentSortModel->getSortRows($parent_sort['scode'] ?? 0);

            $this->view->assign('sort', $this->contentSort);
            $this->view->assign('listsort', $this->contentSort);
            /*验证权限*/
            if ($this->contentSort['gid']) {
                $this->checkPageLevel($this->contentSort['gid'], $this->contentSort['gtype'], $this->contentSort['gnote']);
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

        $this->triggerObserver('AfterGetSort', $this->contentSort);
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
            'sitetplpath' => cdnurl('/template/cms/' . $this->site['theme'], true, false),
            'checkcode' => (string)url('index/captcha'),
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
            'homeurl' => (string)homeurl(),
            'is_home' => 0
        ];

        // BeforeAssignBd 钩子：允许修改全局变量数组
        $hookRes = $this->triggerObserver('BeforeAssignBd', $bdassign);
        if (is_array($hookRes)) {
            $bdassign = array_merge($bdassign, $hookRes);
        }

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

    /**
     * 检查页面权限
     * @param $gcode //权限编码
     * @param $gtype //权限类型
     * @param $gnote //错误提示
     * @return void
     */
    protected function checkPageLevel(int $gcode, string $gtype, string $gnote = ''): void
    {
        if ($gcode) {
            $deny = false;
            $gtype = $gtype ?: 4;
            if ($this->auth->isLogin()) {
                $userInfo = $this->auth->getUserInfo();
            } else {
                $userInfo = [
                    'level' => 0
                ];
            }
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
                if ($this->auth->isLogin()) { // 已经登录
                    $this->error(__($gnote), '/', 1);
                } else {
                    $this->error(__($gnote), '/user/login', 1);
                }
            }
        }
    }

    /**
     * api授权
     * @return array
     */
    protected function apiSecret()
    {
        $timestamp = time();
        $signature = md5(md5(get_sys_config('api_appid') . get_sys_config('api_secret') . $timestamp));
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

        // BeforeArea 钩子：允许插件拦截或修改语言代码
        $hookRes = $this->triggerObserver('BeforeArea', $lg);
        if ($hookRes === false) {
            return;
        }
        if (is_array($hookRes)) {
            $lg = $hookRes[0] ?? $lg;
        }

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

        // AfterArea 钩子：允许最终跳转前修改域名
        $hookRes = $this->triggerObserver('AfterArea', $domain, $cms_area);
        if (is_array($hookRes)) {
            $domain = $hookRes[0] ?? $domain;
        }

        $this->redirect($domain);
    }

    public function likes()
    {
        $id = $this->request->param('id/d', 0);
        if (!$id) {
            $this->error('参数错误');
        }
        if (!cookie('bd_likes_' . $id)) {
            Db::name('cms_content')->where('id', $id)->inc('likes')->update();
            cookie('bd_likes_' . $id, true);
            $this->success('点赞成功');
        }
        $this->error('请勿重复操作');
    }

    public function oppose()
    {
        $id = $this->request->param('id/d', 0);
        if (!$id) {
            $this->error('参数错误');
        }
        if (!cookie('bd_oppose_' . $id)) {
            Db::name('cms_content')->where('id', $id)->inc('oppose')->update();
            cookie('bd_oppose_' . $id, true);
            $this->success('反对成功');
        }
        $this->error('请勿重复操作');
    }

    public function setTheme()
    {
        $theme = isset($this->site['theme']) && !empty($this->site['theme']) ? $this->site['theme'] : 'default';

        /* 设置模版路径 */
        $view_path = root_path() . 'template' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR;
        if (!is_dir($view_path)) {  //兼容public目录下的模版
            $tpl_html_dir = get_sys_config('tpl_html_dir') ?: 'html';
            $view_path = public_path() . 'template' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $tpl_html_dir . DIRECTORY_SEPARATOR;
        }

        // 手机端模版
        if ($this->request->isMobile() && get_sys_config('open_wap') == 1) {
            $view_path .= 'wap' . DIRECTORY_SEPARATOR;
        }

        $this->view = View::instance();
        $this->view->config([
            'view_path' => $view_path,
            'taglib_pre_load' => Bd::class,
            'tpl_replace_string' => array_merge(Config::get('view.tpl_replace_string'), ['__CDN__' => cdnurl('')])
        ]);
    }

    /**
     * 触发观察者事件
     * @param string $event 事件名称
     * @param mixed ...$params 参数
     * @return bool|array 返回 false 表示拦截，返回 array 表示修改后的数据，返回 true 表示通过
     */
    protected function triggerObserver(string $event, ...$params)
    {
        // 自动触发基于控制器类名的系统事件
        // 例如：app\admin\controller\cms\Content.BeforeEdit
        $eventName = static::class . '.' . $event;
        $res1 = Event::trigger($eventName,  $params);
        // 触发高性能通用观察者事件，方便模块进行统一监听
        $res2 = Event::trigger('index_cms_observer.' . $event, ['class' => $eventName, 'params' => $params]);

        // 合并执行结果
        $eventResults = array_merge($res1, $res2);

        // 如果有监听者返回 false，则认为操作被截断
        if (in_array(false, $eventResults, true)) {
            return false;
        }

        // 匹配修改数据的逻辑：寻找返回结果中的第一个数组作为修改后的数据
        foreach ($eventResults as $res) {
            if (is_array($res)) {
                return $res;
            }
        }

        return true;
    }
}

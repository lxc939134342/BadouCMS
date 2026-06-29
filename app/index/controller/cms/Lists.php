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

use modules\cms\library\Bootstrap;

class Lists extends Base
{
    protected $noNeedLogin = ['*'];
    public function index(): string
    {
        $hookRes = $this->triggerObserver('BeforeIndex', $this);
        if ($hookRes === false) {
            return '';
        }
        if ($hookRes !== true && $hookRes !== null) {
            return (string) $hookRes;
        }

        $template = empty($this->contentSort['listtpl']) ? $this->contentSort['contenttpl'] : $this->contentSort['listtpl'];

        $this->site['sitekeywords'] = $this->contentSort['keywords'] ? $this->contentSort['keywords'] : $this->site['sitekeywords'];
        $this->site['sitedescription'] = $this->contentSort['description'] ? $this->contentSort['description'] : $this->site['sitedescription'];
        $sorttitle = $this->contentSort['title'] ? $this->contentSort['title'] : $this->contentSort['name'];

        // 页面标题
        $list_title = get_sys_config('list_title');
        if ($list_title) {
            $pagetitle = $this->view->display($list_title, ['sorttitle' => $sorttitle]);
        } else {
            // 过滤空值避免多余的分隔符
            $titleParts = array_filter([$sorttitle, $this->site['sitetitle']]);
            $pagetitle = implode('-', $titleParts);
        }

        $this->site['pagetitle'] = $pagetitle;
        $this->site['pagedescription'] = $this->site['sitedescription'];
        $this->site['pagekeywords'] = $this->site['sitekeywords'];
        $bootstrap = new Bootstrap(0, 10);
        $page = $bootstrap->pageData();
        $this->view->assign('page', $page);
        $this->assignBd();
        $template = basename($template, '.html');

        $hookRes = $this->triggerObserver('AfterIndex', $this, $template);
        if ($hookRes !== true && $hookRes !== null) {
            return (string) $hookRes;
        }

        return $this->view->fetch('/' . $template);
    }
}

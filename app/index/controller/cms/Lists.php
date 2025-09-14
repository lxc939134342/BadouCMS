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
        $template = empty($this->contentSort['listtpl']) ? $this->contentSort['contenttpl'] : $this->contentSort['listtpl'];
        $pagetitle = $this->contentSort['title'] ? $this->contentSort['title'] : $this->contentSort['name']; // 页面标题

        // 过滤空值避免多余的分隔符
        $titleParts = array_filter([$pagetitle, $this->site['sitetitle'], $this->site['sitesubtitle']]);
        $pagetitle = implode('-', $titleParts);

        $this->site['sitekeywords'] = $this->contentSort['keywords'] ? $this->contentSort['keywords'] : $this->site['sitekeywords'];
        $this->site['sitedescription'] = $this->contentSort['description'] ? $this->contentSort['description'] : $this->site['sitedescription'];

        $this->site['pagetitle'] = $pagetitle;
        $this->site['pagedescription'] = $this->site['sitedescription'];
        $this->site['pagekeywords'] = $this->site['sitekeywords'];
        $bootstrap = new Bootstrap(0, 10);
        $page = $bootstrap->pageData();
        $this->view->assign('page', $page);
        $this->assignBd();
        return $this->view->fetch('/'.basename($template, '.html'));
    }
}

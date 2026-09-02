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

class Index extends Base
{
    protected $noNeedLogin = ['*'];

    public function index()
    {
        $index_title = get_sys_config('index_title');
        if ($index_title) {
            $pagetitle = $this->view->display($index_title);
        } else {
            // 过滤空值避免多余的分隔符
            $titleParts = array_filter([$this->site['sitetitle'], $this->site['sitesubtitle']]);
            $pagetitle = implode('-', $titleParts);
        }

        $this->pageSeo = [
            'pagetitle' => $pagetitle,
            'pagedescription' => $this->site['sitedescription'],
            'pagekeywords' => $this->site['sitekeywords'],
            'is_home' => 1,
        ];
        $this->assignBd();

        return $this->view->fetch('/index');
    }
}

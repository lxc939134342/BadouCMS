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
    protected array $noNeedLogin = ['*'];

    public function index()
    {
        $this->site['sitetitle'] = $this->site['sitetitle'] . '-' . $this->site['sitesubtitle'];
        $this->assignBd();
        return $this->view->fetch('/index');
    }
}

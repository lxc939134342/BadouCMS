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

class Search extends Base
{
    protected array $noNeedLogin = ['*'];

    public function index()
    {
        $tpl = 'search.html';
        /*指定搜索页面*/
        $searchtpl = $this->request->param('searchtpl');

        if (!empty($searchtpl)) {
            if (!preg_match('/^[\w]+\.html$/', $searchtpl)) {
                $searchtpl = 'search.html';
            }
            $tpl = $searchtpl;
        }

        $keyword = $this->request->param('keyword', $this->request->param('title'));
        if (empty($keyword)) {
            $this->error(__('Please enter keywords'));
        }
        //禁止搜索过滤域名
        if (preg_match("/\.[a-z]{2,}/i", $keyword)) {
            $this->error(__('No Data'));
        }
        $keyword = strip_tags($keyword);
        $keyword = str_replace(strrchr($keyword, "."), "", $keyword);  //去掉带有后缀的关键词
        $keyword = mb_substr($keyword, 0, 15);
        $this->view->assign('keyword', $keyword);

        return $this->view->fetch('/'.basename($tpl, '.html'));
    }
}

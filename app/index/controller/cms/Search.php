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
    protected $noNeedLogin = ['*'];

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
        // 处理关键词，区分文件后缀和数字中的小数点
        $lastDotPos = strrpos($keyword, ".");
        if ($lastDotPos !== false) {
            $suffix = substr($keyword, $lastDotPos + 1);
            // 如果后缀不是纯数字，则认为是文件后缀，去掉它
            if (!is_numeric($suffix)) {
                $keyword = substr($keyword, 0, $lastDotPos);
            }
        }
        $keyword = mb_substr($keyword, 0, 15);
        $this->view->assign('keyword', $keyword);

        // 页面标题
        $search_title = get_sys_config('search_title');
        if ($search_title) {
            $pagetitle = $this->view->display($search_title);
        } else {
            $pagetitle = '搜索-' . $this->site['sitetitle'];
        }
        $this->pageSeo = [
            'pagetitle' => $pagetitle,
            'pagedescription' => $this->site['sitedescription'],
            'pagekeywords' => $this->site['sitekeywords'],
        ];
        $this->assignBd();
        return $this->view->fetch('/' . basename($tpl, '.html'));
    }
}

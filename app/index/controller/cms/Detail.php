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

use app\index\model\cms\Content;
use app\index\model\cms\ContentSort;

class Detail extends Base
{
    protected $noNeedLogin = ['*'];
    /**
     * @var \app\index\model\cms\Content
     */
    protected $model = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new Content();
    }


    public function index()
    {
        $id = $this->request->param('id');
        if (!$this->contentInfo) {
            $this->contentInfo = $this->model::getContent($this->contentSort['scode'], $id);
            if (!$this->contentInfo) {
                /*内容不存在*/
                abort(404, __("Not found"));
            }
        }
        // 避免内容设置自定义 URL 后，旧 ID 地址仍直接展示造成重复页面。
        $this->redirectCanonicalUrl();
        /*验证内容权限*/
        if ($this->contentInfo['gid']) {
            $this->checkPageLevel($this->contentInfo['gid'], $this->contentInfo['gtype'], $this->contentInfo['gnote']);
        }

        $this->contentInfo = $this->contentInfo->toArray();
        /* 上一页 下一页 */
        $contentSortModel = new ContentSort();
        $scodes = $contentSortModel->getSubScodes($this->contentInfo['scode']);
        $next = $this->model::getContentPreNext($scodes, $this->contentInfo['id'], 'next');
        $pre = $this->model::getContentPreNext($scodes, $this->contentInfo['id'], 'pre');
        $this->contentInfo = array_merge($this->contentInfo, $next, $pre);

        $this->site['sitekeywords'] = $this->contentInfo['keywords'] ? $this->contentInfo['keywords'] : $this->site['sitekeywords'];
        $this->site['sitedescription'] = $this->contentInfo['description'] ? $this->contentInfo['description'] : $this->site['sitedescription'];
        $this->contentInfo['sortlink'] = $this->contentSort['link'];
        $this->view->assign('content', $this->contentInfo);

        $sorttitle = $this->contentSort['title'] ? $this->contentSort['title'] : $this->contentSort['name']; // 栏目标题

        // 页面标题
        $content_title = get_sys_config('content_title');
        if ($content_title) {
            $pagetitle = $this->view->display($content_title, ['sorttitle' => $sorttitle]);
        } else {
            // 过滤空值避免多余的分隔符
            $titleParts = array_filter([$this->contentInfo['title'], $sorttitle, $this->site['sitetitle']]);
            $pagetitle = implode('-', $titleParts);
        }

        $this->site['pagetitle'] = $pagetitle;
        $this->site['pagedescription'] = $this->site['sitedescription'];
        $this->site['pagekeywords'] = $this->site['sitekeywords'];

        $this->assignBd();
        $custom_tpl = isset($this->contentInfo['custom_tpl']) && !empty($this->contentInfo['custom_tpl']) ? $this->contentInfo['custom_tpl'] : false;
        $template = $custom_tpl ?: $this->contentSort['contenttpl'];
        return $this->view->fetch('/' . basename($template, '.html'));
    }

    protected function redirectCanonicalUrl(): void
    {
        if (!$this->contentInfo) {
            return;
        }

        // 跳转目标允许站内相对地址和 http/https 站外地址，避免其它协议或异常字符进入 Location。
        $canonicalUrl = (string)$this->contentInfo['link'];
        $canonicalParts = parse_url($canonicalUrl);
        if ($canonicalUrl === '' || $canonicalParts === false || preg_match("/[\r\n]/", $canonicalUrl)) {
            return;
        }

        $canonicalScheme = strtolower($canonicalParts['scheme'] ?? '');
        if ($canonicalScheme && !in_array($canonicalScheme, ['http', 'https'])) {
            return;
        }

        $canonicalHost = strtolower($canonicalParts['host'] ?? '');
        if ($canonicalHost && $canonicalHost !== strtolower($this->request->host())) {
            $this->redirect($canonicalUrl, 301);
        }

        $canonicalPath = $this->normalizePath($canonicalParts['path'] ?? '');
        $currentPath = $this->normalizePath(parse_url($this->request->url(), PHP_URL_PATH) ?: '');

        if (!$canonicalPath || !$currentPath || $canonicalPath === $currentPath) {
            return;
        }

        $this->redirect($canonicalUrl, 301);
    }

    protected function normalizePath(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }
}

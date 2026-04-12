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

use app\index\model\cms\ContentSort;
use app\index\model\cms\Content;
use think\Response;

class Sitemap extends Base
{
    protected $noNeedLogin = ['*'];
    protected $model = null;

    public function index(): Response
    {
        $list = [];
        $list[] = $this->makeNode('', date('Y-m-d'), '1.00', 'always'); // 根目录
        $sortModel = new ContentSort();
        $contentModel = new Content();
        $sorts = $sortModel->getSortList();
        foreach ($sorts as $value) {
            if ($value['outlink']) {
                continue;
            } elseif ($value['type'] == 1) {
                $list[] = $this->makeNode($value['link'], date('Y-m-d'), '0.80', 'daily');
            } else {
                $list[] = $this->makeNode($value['link'], date('Y-m-d'), '0.80', 'daily');
                $contents = $contentModel->getSortContent($value['scode']);
                if (!$contents->isEmpty()) {
                    foreach ($contents as $value2) {
                        if ($value2['outlink']) { // 外链
                            continue;
                        }
                        $list[] = $this->makeNode($value2['link'], date('Y-m-d', strtotime($value2['date'])), '0.60', 'daily');
                    }
                }
            }
        }

        return response($list, 200, [], 'xml')->options([
            'root_node' => 'urlset',
            'item_node' => 'url',
            'root_attr' => ['xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9'],
            'item_key'  => ''
        ]);
    }

    private function makeNode($link, $date, $priority = 0.60, $changefreq = 'always')
    {
        return [
            'loc'        => empty($link) ? $this->request->domain() : $this->domainurl(htmlspecialchars($link, ENT_XML1 | ENT_QUOTES, 'UTF-8')),
            'lastmod'    => $date,
            'changefreq' => $changefreq,
            'priority'   => $priority,
        ];
    }

    protected function domainurl($url)
    {
        if (!$url) {
            return $url;
        }

        return $this->request->domain(). $url;
    }

    // 文本格式
    public function txt(): void
    {
        header("Content-Type: text/plain");
        header("Content-Disposition: inline");
        $sortModel = new ContentSort();
        $contentModel = new Content();
        $sorts = $sortModel->getSortList();
        $str = $this->request->domain() . PHP_EOL; // 根目录
        foreach ($sorts as $value) {
            if ($value['outlink']) {
                continue;
            }

            $str .= $this->domainurl($value['link']) . PHP_EOL;

            if ($value['type'] != 1) {
                $contents = $contentModel->getSortContent($value['scode']);
                foreach ($contents as $value2) {
                    if ($value2['outlink']) { // 外链
                        continue;
                    }
                    $str .= $this->domainurl($value2['link']) . PHP_EOL;
                }
            }
        }
        echo $str;
        exit;
    }
}

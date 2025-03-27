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
    protected array $noNeedLogin = ['*'];
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

        return response($list, 200, [], 'xml');
    }

    private function makeNode($link, $date, $priority = 0.60)
    {
        return [
            'loc'      => $this->domainurl($link),
            'priority' => $priority,
            'lastmod' => $date,
            'changefreq' => 'Always'
        ];
    }

    protected function domainurl($url)
    {
        if (!$url) {
            return $url;
        }

        return $this->request->scheme().':'. $url;
    }

    // 文本格式
    public function txt(): void
    {
        header("Content-Type: text/plain");
        header("Content-Disposition: inline");
        $sortModel = new ContentSort();
        $contentModel = new Content();
        $sorts = $sortModel->getSortList();
        $str = "";
        foreach ($sorts as $value) {
            if ($value['outlink']) {
                continue;
            } elseif ($value['type'] == 1) {
                $str .= $value['link'] . PHP_EOL;
            } else {
                $link = $value['link'];
                $str .= $link . PHP_EOL;
                $contents = $contentModel->getSortContent($value['scode']);
                foreach ($contents as $value2) {
                    if ($value2['outlink']) { // 外链
                        continue;
                    } else {
                        $link = $value2['link'];
                    }
                    $str .= $link . PHP_EOL;
                }
            }
        }
        echo $str;
        exit;
    }

}

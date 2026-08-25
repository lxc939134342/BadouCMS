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

use app\index\model\cms\Area;
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
        $sortModel = new ContentSort();
        $contentModel = new Content();
        $originalLanguage = get_frontend_lang();
        $areas = $this->getAreas($originalLanguage);

        // 先为每个已启用语言添加首页，再处理栏目和内容。
        foreach ($areas as $language => $area) {
            $language = (string)($area['acode'] ?? $language);
            if ($language !== '') {
                $list[] = $this->makeNode('', date('Y-m-d'), '1.00', 'always', $language, $area, true);
            }
        }

        foreach ($sortModel->getSortListAll() as $value) {
            $language = (string)($value['acode'] ?? '');
            if ($language === '' || !isset($areas[$language]) || $value['outlink']) {
                continue;
            }

            $area = $areas[$language];
            $list[] = $this->makeNode($value['link'], date('Y-m-d'), '0.80', 'daily', $language, $area);
            if ($value['type'] == 1) {
                continue;
            }

            $contents = $contentModel->getSortContent($value['scode'], $language);
            foreach ($contents as $value2) {
                if (!empty($value2['outlink'])) { // 外链
                    continue;
                }
                $list[] = $this->makeNode(
                    $value2['link'],
                    date('Y-m-d', strtotime($value2['date'])),
                    '0.60',
                    'daily',
                    $language,
                    $area
                );
            }
        }

        return response($list, 200, [], 'xml')->options([
            'root_node' => 'urlset',
            'item_node' => 'url',
            'root_attr' => ['xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9'],
            'item_key'  => ''
        ]);
    }

    /**
     * 获取所有已启用的前台语言；没有区域配置时保留原有单语言行为。
     */
    protected function getAreas(string $originalLanguage): array
    {
        $areas = (new Area())->getList();
        if (!empty($areas)) {
            return $areas;
        }

        return [
            $originalLanguage => [
                'acode'      => $originalLanguage,
                'domain'     => '',
                'is_default' => $originalLanguage === get_default_lang() ? '1' : '0',
            ],
        ];
    }

    private function makeNode(
        string $link,
        string $date,
        string $priority = '0.60',
        string $changefreq = 'always',
        string $language = '',
        array $area = [],
        bool $root = false
    ): array {
        $url = $this->buildUrl($link, $language, $area, $root);

        return [
            'loc'        => htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8'),
            'lastmod'    => $date,
            'changefreq' => $changefreq,
            'priority'   => $priority,
        ];
    }

    /**
     * 按语言生成完整 URL：
     * - 配置了独立域名时使用该语言域名；
     * - 目录模式使用 /{语言}/；
     * - 非目录模式使用 ?lg={语言}。
     */
    protected function buildUrl(string $link, string $language, array $area = [], bool $root = false): string
    {
        $language = trim($language);
        $defaultLanguage = trim(get_default_lang());
        $areaDomain = trim((string)($area['domain'] ?? ''));
        $hasIndependentDomain = $areaDomain !== '';
        $domain = $this->getLanguageDomain($area);
        $urlRuleType = (int)get_sys_config('url_rule_type');

        if ($root) {
            if ($hasIndependentDomain || $language === '' || $language === $defaultLanguage) {
                return $domain;
            }

            if ($urlRuleType === 1) {
                return $domain . '/' . rawurlencode($language) . '/';
            }

            return $domain . '/?lg=' . rawurlencode($language);
        }

        $link = (string)$link;
        if ($link === '') {
            return $domain;
        }

        // bdurl() 在目录模式下已经添加了语言前缀。独立域名本身已完成语言识别，去掉该前缀。
        if ($hasIndependentDomain && $urlRuleType === 1 && $language !== '' && $language !== $defaultLanguage) {
            $prefix = '/' . preg_quote(rawurlencode($language), '#') . '(?=/|$)';
            $link = preg_replace('#^' . $prefix . '#', '', $link) ?: $link;
        }

        if (!$hasIndependentDomain && $urlRuleType !== 1 && $language !== '' && $language !== $defaultLanguage) {
            $separator = str_contains($link, '?') ? '&' : '?';
            $link .= $separator . 'lg=' . rawurlencode($language);
        }

        return $domain . '/' . ltrim($link, '/');
    }

    protected function getLanguageDomain(array $area = []): string
    {
        $domain = trim((string)($area['domain'] ?? ''));
        if ($domain === '') {
            return rtrim($this->request->domain(), '/');
        }

        if (!preg_match('/^https?:\/\//i', $domain)) {
            $domain = $this->request->scheme() . '://' . $domain;
        }

        return rtrim($domain, '/');
    }

    // 文本格式
    public function txt(): void
    {
        header("Content-Type: text/plain");
        header("Content-Disposition: inline");

        $sortModel = new ContentSort();
        $contentModel = new Content();
        $originalLanguage = get_frontend_lang();
        $areas = $this->getAreas($originalLanguage);
        $str = '';

        foreach ($areas as $language => $area) {
            $language = (string)($area['acode'] ?? $language);
            if ($language !== '') {
                $str .= $this->buildUrl('', $language, $area, true) . PHP_EOL;
            }
        }

        foreach ($sortModel->getSortListAll() as $value) {
            $language = (string)($value['acode'] ?? '');
            if ($language === '' || !isset($areas[$language]) || $value['outlink']) {
                continue;
            }

            $area = $areas[$language];
            $str .= $this->buildUrl($value['link'], $language, $area) . PHP_EOL;
            if ($value['type'] == 1) {
                continue;
            }

            $contents = $contentModel->getSortContent($value['scode'], $language);
            foreach ($contents as $value2) {
                if (!empty($value2['outlink'])) { // 外链
                    continue;
                }
                $str .= $this->buildUrl($value2['link'], $language, $area) . PHP_EOL;
            }
        }

        echo $str;
        exit;
    }
}

<?php

namespace bd;

use think\Paginator;

class Bootstrap extends Paginator
{
    /**
     * 上一页按钮
     * @param string $text
     * @return string
     */
    protected function getPreviousButton($text = '')
    {
        $text = $text ?: __('Previous Page');

        if ($this->currentPage() <= 1) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = $this->url(
            $this->currentPage() - 1
        );

        return $this->getPageLinkWrapper($url, $text);
    }

    //总数标签
    protected function totalshow()
    {
        if (!$this->lastPage) {
            return "";
        }
        $totalhtml = "<li class='page-item disabled'><span class='page-link'>" . __('Page %s of %s', [$this->currentPage(), $this->lastPage()]) . "</span></li>";
        return $totalhtml;

    }

    //尾页标签
    protected function showlastpage($text = '')
    {
        $text = $text ?: __('Last Page');
        if ($this->currentPage() == $this->lastPage()) {
            return $this->getDisabledTextWrapper($text);

        }

        $url = $this->url($this->lastPage());
        return $this->getPageLinkWrapper($url, $text);
    }

    //首页标签
    protected function showfirstpage($text = '')
    {

        if ($this->currentPage() == 1) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = $this->url(1);
        return $this->getPageLinkWrapper($url, $text);
    }
    //后五页
    protected function afivepage($text = '')
    {
        $text = $text ?: __('Next Five Pages');
        if ($this->lastPage() < $this->currentPage() + 5) {
            return $this->getDisabledTextWrapper($text);

        }
        $url = $this->url($this->currentPage() + 5);


        return $this->getPageLinkWrapper($url, $text);
    }

    //前五页
    protected function bfivepage($text = '')
    {
        $text = $text ?: __('Previous Five Pages');
        if ($this->currentPage() < 5) {
            return $this->getDisabledTextWrapper($text);
        }
        $url = $this->url($this->currentPage() - 5);
        return $this->getPageLinkWrapper($url, $text);
    }

    /**
     * 下一页按钮
     * @param string $text
     * @return string
     */
    protected function getNextButton($text = '')
    {
        $text = $text ?: __('Next Page');
        if (!$this->hasMore) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = $this->url($this->currentPage() + 1);

        return $this->getPageLinkWrapper($url, $text);
    }

    //跳转到哪页
    protected function gopage()
    {
        return "<li class='page-item'><form class='jumpto' action='' method='get' ><input type='text' class='page_number' name='page' placeholder='".__('Page Number')."'> <input type='submit' class='submit' value='".__('Jump')."'> </form></li>";
    }

    /**
     * 页码按钮
     * @return string
     */
    protected function getLinks()
    {
        if ($this->simple) {
            return '';
        }

        $block = [
            'first'  => null,
            'slider' => null,
            'last'   => null
        ];

        $side   = 2;
        $window = $side * 2;

        if ($this->lastPage < $window + 1) {
            $block['slider'] = $this->getUrlRange(1, $this->lastPage);

        } elseif ($this->currentPage <= $window - 1) {

            $block['slider'] = $this->getUrlRange(1, $window + 1);
        } elseif ($this->currentPage > ($this->lastPage - $window + 1)) {
            $block['slider']  = $this->getUrlRange($this->lastPage - ($window), $this->lastPage);

        } else {

            $block['slider'] = $this->getUrlRange($this->currentPage - $side, $this->currentPage + $side);
        }

        $html = '';

        if (is_array($block['first'])) {
            $html .= $this->getUrlLinks($block['first']);
        }

        if (is_array($block['slider'])) {

            $html .= $this->getPageNumberLinks($block['slider']);
        }

        if (is_array($block['last'])) {
            $html .= $this->getUrlLinks($block['last']);
        }

        return $html;
    }

    /**
     * 渲染分页html
     * @return mixed
     */
    public function render()
    {
        if ($this->hasPages()) {
            if ($this->simple) {
                return sprintf(
                    '<ul class="pager">%s %s </ul>',
                    $this->getPreviousButton(__('Previous Page')),
                    $this->getNextButton(__('Next Page'))
                );
            } else {
                return sprintf(
                    '<ul class="pagination justify-content-center"> %s %s %s %s %s </ul>',
                    //显示数量页码信息
                    $this->totalshow(),
                    //第一页
                    // $this->showfirstpage(__('Home Page')),
                    //上一页
                    $this->getPreviousButton(),
                    //前五页
                    //                    $this->bfivepage(),
                    //页码
                    $this->getLinks(),
                    //后五页
                    //    $this->afivepage(),
                    //下一页
                    $this->getNextButton(),
                    //最后一页
                    // $this->showlastpage(__('Last Page')),
                    //最后再加个参数 %s 可以显示跳转到哪页
                    $this->gopage()
                );
            }
        } else {  //没有分页
            if (!$this->simple) { // 非简洁分页的情况
                return sprintf(
                    '<ul class="pagination justify-content-center"> %s </ul>',
                    //显示数量页码信息
                    $this->totalshow()
                );
            }
        }
    }

    /* 生成分页数组 */
    public function pageData()
    {
        return [
            'index'   => $this->url(1),
            'pre'     => $this->url($this->currentPage() - 1),
            'next'    => $this->url($this->currentPage() + 1),
            'last'    => $this->url($this->lastPage),
            'bar'     => $this->render(),
            'current' => $this->currentPage(),
            'count'   => $this->lastPage(),
            'rows'    => $this->total,
            'number' => $this->getLinks(),
            'numbar' => $this->getLinks(),
            'selectbar' => $this->gopage()
        ];
    }

    /**
     * 生成一个可点击的按钮
     *
     * @param  string $url
     * @param  int    $page
     * @return string
     */
    protected function getAvailablePageWrapper($url, $page)
    {
        return '<li class="page-item"><a class="page-link" href="' . htmlentities($url) . '">' . $page . '</a></li>';
    }

    /**
     * 生成一个禁用的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<li class="page-item disabled"><span class="page-link">' . $text . '</span></li>';
    }

    /**
     * 生成一个激活的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<li class="active"><span>' . $text . '</span></li>';
    }

    /**
     * 生成省略号按钮
     *
     * @return string
     */
    protected function getDots($text = '...')
    {

        //$url = $this->url($this->currentPage() + 1);

        //  return $this->getPageLinkWrapper($url, $text);
        return $this->getDisabledTextWrapper('...');
    }

    /**
     * 批量生成页码按钮.
     *
     * @param  array $urls
     * @return string
     */
    protected function getUrlLinks(array $urls)
    {
        $html = '';

        foreach ($urls as $page => $url) {
            $html .= $this->getPageLinkWrapper($url, $page);
        }

        return $html;
    }

    /**
     * 批量生成数字页码按钮.
     *
     * @param array $urls
     * @return string
     */
    protected function getPageNumberLinks(array $urls): string
    {
        $html = '';

        foreach ($urls as $page => $url) {
            if ($page == $this->currentPage()) {
                $html .= '<li class="page-item active page-num"><a class="page-link" >' . $page . '</a></li>';
            } else {
                $html .= '<li class="page-item page-num"><a class="page-link" href="' . htmlentities($url) . '">' . $page . '</a></li>';
                ;
            }

        }

        return $html;
    }

    /**
     * 生成普通页码按钮
     *
     * @param  string $url
     * @param  int    $page
     * @return string
     */
    protected function getPageLinkWrapper($url, $page)
    {
        if ($page == $this->currentPage()) {
            return $this->getActivePageWrapper($page);
        }

        return $this->getAvailablePageWrapper($url, $page);
    }
}

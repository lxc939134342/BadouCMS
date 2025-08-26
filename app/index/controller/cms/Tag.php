<?php

namespace app\index\controller\cms;

class Tag extends Base
{
    protected $noNeedLogin = ['*'];
    public function index()
    {
        if (!$this->request->param('tag')) {
            abort(404, __('Not found'));
        }

        $tagstpl = $this->request->param('tagstpl', 'tags.html');

        if (!empty($tagstpl)) {
            if (!preg_match('/^[\w]+\.html$/', $tagstpl)) {
                $tagstpl = 'tags.html';
            }
            $tpl = $tagstpl;
        }

        return $this->view->fetch('/'.basename($tpl, '.html'));
    }
}

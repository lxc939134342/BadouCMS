<?php

namespace app\api\controller\cms;

use app\common\controller\Api;

class Lists extends Api
{
    protected array $noNeedLogin = ['*'];
    public function index()
    {
        p($this->request->param());
        $this->success('success', '');
    }
}

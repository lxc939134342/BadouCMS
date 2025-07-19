<?php

namespace app\api\controller\cms;

use app\common\controller\Frontend;
use badou\qrcode\QRcodeLib;

class Qrcode extends Frontend
{
    protected $noNeedLogin = ['*'];
    public function index()
    {
        $string = $this->request->param('string');
        if (!$string) {
            $this->error('参数错误');
        }

        return QRcodeLib::png($string, false, 'M', 6, 1);
    }
}

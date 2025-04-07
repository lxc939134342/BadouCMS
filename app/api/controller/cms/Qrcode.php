<?php

namespace app\api\controller\cms;

use app\common\controller\Frontend;
use bd\qrcode\QRcodeLib;

class Qrcode extends Frontend
{
    protected array $noNeedLogin = ['*'];
    protected array $noNeedPermission = ['*'];
    public function index()
    {
        $string = $this->request->param('string');
        if (!$string) {
            $this->error('参数错误');
        }

        return QRcodeLib::png($string, false, 'M', 6, 1);
    }
}

<?php

namespace app\api\controller\cms;

use ba\Random;
use Throwable;
use ba\Captcha;
use think\Response;
use app\common\controller\Api;

class Common extends Api
{
    /**
     * 图形验证码
     * @throws Throwable
     */
    public function captcha(): Response
    {
        $captchaId = $this->request->request('id', Random::uuid());
        $config    = array(
            'codeSet'  => '123456789',            // 验证码字符集合
            'fontSize' => 22,                     // 验证码字体大小(px)
            'useCurve' => false,                  // 是否画混淆曲线
            'useNoise' => true,                   // 是否添加杂点
            'length'   => 4,                      // 验证码位数
            'bg'       => array(255, 255, 255),   // 背景颜色
        );

        $captcha = new Captcha($config);
        session('captchaId', $captchaId);
        return $captcha->entry($captchaId);
    }
}

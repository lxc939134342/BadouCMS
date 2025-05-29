<?php

namespace badou;

use think\Exception;

/**
 * 插件异常处理类
 */
class ModuleException extends Exception
{
    public function __construct($message, $code = 0, $data = '')
    {
        $this->message  = $message;
        $this->code     = $code;
        $this->data     = $data;
    }

}

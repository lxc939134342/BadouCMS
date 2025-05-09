<?php
namespace app;

// 应用请求对象类
class Request extends \think\Request
{
    /**
     * 全局过滤规则
     * app/common.php 的 filter 函数
     */
    protected $filter = 'filter';
}

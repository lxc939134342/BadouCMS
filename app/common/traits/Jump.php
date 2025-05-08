<?php

namespace app\common\traits;

use think\exception\HttpResponseException;
use think\Response;

trait Jump
{
    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param string $url 跳转的 URL 地址
     * @param mixed $data 返回的数据
     * @param int $wait 跳转等待时间
     * @param array $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        if (is_null($url) && !is_null($this->request->server('HTTP_REFERER'))) {
            $url = $this->request->server('HTTP_REFERER');
        } elseif ('' !== $url && !strpos($url ?? '', '://') && 0 !== strpos($url ?? '', '/')) {
            $url = (string)url($url ?? '');
        }

        $type   = $this->getResponseType();
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        if ('html' == strtolower($type)) {
            $response = Response::create($this->app->config->get('badouadmin.jump.dispatch_success_tmpl'), 'view')
                ->assign($result)
                ->header($header);
        } else {
            $response = Response::create($result, $type)->header($header);
        }

        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param string $url 跳转的 URL 地址
     * @param mixed $data 返回的数据
     * @param int $wait 跳转等待时间
     * @param array $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function error($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        if (is_null($url)) {
            $url = $this->request->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ('' !== $url && !strpos($url, '://') && 0 !== strpos($url, '/')) {
            $url = (string)url($url);
        }

        $type   = $this->getResponseType();
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];
        if ('html' == strtolower($type)) {
            $response = Response::create($this->app->config->get('badouadmin.jump.dispatch_error_tmpl'), 'view')
                ->assign($result)
                ->header($header);
        } else {
            $response = Response::create($result, $type)->header($header);
        }

        throw new HttpResponseException($response);
    }

    /**
     * 返回结果
     * @param string $msg
     * @param array $data
     * @param int $code
     */
    protected function result($msg='',$data=[],$count=0,$code=0)
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'count'=>$count?:count($data)
        ];
        $response = Response::create($result, 'json');
        throw new HttpResponseException($response);
    }

    /**
     * URL重定向
     * @access protected
     * @param  string $url 跳转的URL表达式
     * @param  integer $code http code
     * @param  array $with 隐式传参
     * @return void
     */
    protected function redirect($url, $code = 302, $with = [])
    {
        $response = Response::create($url, 'redirect');
        $response->code($code)->with($with);
        throw new HttpResponseException($response);
    }

    /**
     * 获取当前的 response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        return $this->isAjax()
            ? 'json'
            : 'html';
    }
}

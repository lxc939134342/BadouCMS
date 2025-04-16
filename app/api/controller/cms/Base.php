<?php

namespace app\api\controller\cms;

use think\Response;
use app\common\controller\Api;
use think\exception\HttpResponseException;

class Base extends Api
{
    public function initialize(): void
    {
        parent::initialize();
        // 加载控制器语言包
        $langSet = $this->app->lang->getLangSet();
        $app_index_path = root_path().'app'.DIRECTORY_SEPARATOR.'index'.DIRECTORY_SEPARATOR;
        $this->app->lang->load([
              $app_index_path.'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . (str_replace('/', DIRECTORY_SEPARATOR, $this->app->request->controllerPath)) . '.php',
              $app_index_path . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR.'cms'.DIRECTORY_SEPARATOR.'index.php'
        ]);
        $this->checkAccess();
    }

    /**
     * 认证api
     */
    public function checkAccess()
    {
        $config = get_sys_config();
        // 判断用户
        if (!$config['api_appid']) {
            $this->error('请求失败：管理后台接口认证用户配置有误', '', 403);
        }
        // 判断密钥
        if (!$config['api_secret']) {
            $this->error('请求失败：管理后台接口认证密钥配置有误', '', 403);
        }
        $appid = $this->request->param('appid') ? $this->request->param('appid') : $this->request->header('appid');
        $timestamp = $this->request->param('timestamp') ? $this->request->param('timestamp') : $this->request->header('timestamp');
        $signature = $this->request->param('signature') ? $this->request->param('signature') : $this->request->header('signature');
        // 获取参数
        if (!$appid) {
            $this->error('请求失败：未检查到appid参数', '', 403);
        }
        if (!$timestamp) {
            $this->error('请求失败：未检查到timestamp参数', '', 403);
        }
        if (!$signature) {
            $this->error('请求失败：未检查到signature参数', '', 403);
        }

        $host = request()->scheme()."://".request()->host(true);
        // 验证时间戳
        if (strpos($_SERVER['HTTP_REFERER'], $host) === false && time() - $timestamp > 15) { // 请求时间戳认证，不得超过15秒
            $this->error('请求失败：接口时间戳验证失败！', '', 403);
        }

        // 验证签名
        if ($signature != md5(md5($config['api_appid'] . $config['api_secret'] . $timestamp))) {
            $this->error('请求失败：接口签名信息错误！', '', 403);
        }
    }

    /**
     * 操作成功
     * @param string      $msg     提示消息
     * @param mixed       $data    返回数据
     * @param int         $code    错误码
     * @param string|null $type    输出类型
     * @param array       $header  发送的 header 信息
     * @param array       $options Response 输出参数
     */
    protected function success(string $msg = '', mixed $data = null, int $code = 1, string $type = null, array $header = [], array $options = []): void
    {
        $this->result($msg, $data, $code, $type, $header, $options);
    }

    /**
     * 操作失败
     * @param string      $msg     提示消息
     * @param mixed       $data    返回数据
     * @param int         $code    错误码
     * @param string|null $type    输出类型
     * @param array       $header  发送的 header 信息
     * @param array       $options Response 输出参数
     */
    protected function error(string $msg = '', mixed $data = null, int $code = 0, string $type = null, array $header = [], array $options = []): void
    {
        $this->result($msg, $data, $code, $type, $header, $options);
    }


    /**
     * 返回 API 数据
     * @param string      $msg     提示消息
     * @param mixed       $data    返回数据
     * @param int         $code    错误码
     * @param string|null $type    输出类型
     * @param array       $header  发送的 header 信息
     * @param array       $options Response 输出参数
     */
    public function result(string $msg, mixed $data = null, int $code = 0, string $type = null, array $header = [], array $options = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => $this->request->server('REQUEST_TIME'),
            'data' => $data,
            'tourl' => $data['tourl'] ?? '',
            'rowtotal' => $data['total'] ?? 0,
        ];

        $type = $type ?: $this->responseType;
        $code = $header['statusCode'] ?? 200;

        $response = Response::create($result, $type, $code)->header($header)->options($options);
        throw new HttpResponseException($response);
    }
}

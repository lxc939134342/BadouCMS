<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Attachment;
use OSS\OssClient;
use think\facade\Log;
use app\common\library\Upload;
use app\common\exception\UploadException;

class Alioss extends Api
{
    protected $noNeedRight = ['upload'];

    public function upload()
    {
        $config = get_sys_config('', 'alioss');
        $upload = \app\common\model\Config::upload();
        $oss = new OssClient($config['alioss_access_key_id'], $config['alioss_access_key_secret'], $config['alioss_endpoint']);

        $attachment = null;
        //默认普通上传文件
        $file = $this->request->file('file');
        try {
            $upload = new Upload($file);
            $attachment = $upload->upload();
        } catch (UploadException $e) {
            $this->error($e->getMessage());
        }

        //文件绝对路径
        $filePath = $upload->getFile()->getRealPath() ?: $upload->getFile()->getPathname();

        try {
            $ret = $oss->uploadFile($config['alioss_bucket'], ltrim($attachment->url, "/"), $filePath);
            //成功不做任何操作
        } catch (\Exception $e) {
            $this->delFile($attachment, $upload);

            Log::write($e->getMessage());
            $this->error("上传失败(1002)");
        }
        $this->delFile($attachment, $upload);

        // 记录云存储记录
        $data = $attachment->toArray();
        unset($data['id']);
        unset($data['create_time']);
        unset($data['update_time']);
        unset($data['thumb_style']);
        $data['storage'] = 'alioss';
        Attachment::create($data);

        $this->success(__('Uploaded successful'), ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);
    }

    protected function delFile($attachment, $upload)
    {
        if ($attachment && !empty($attachment['id'])) {
            $attachment->delete();
        }
        if ($upload) {
            //文件绝对路径
            $filePath = $upload->getFile()->getRealPath() ?: $upload->getFile()->getPathname();
            @unlink($filePath);
        }
    }
}

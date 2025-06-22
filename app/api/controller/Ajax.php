<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Upload;
use app\common\exception\UploadException;

class Ajax extends Api
{
    protected $noNeedRight = ['upload'];

    public function upload()
    {
        $upload = \app\common\model\Config::upload();

        $attachment = null;
        //默认普通上传文件
        $file = $this->request->file('file');
        try {
            $upload = new Upload($file);
            $attachment = $upload->upload();
        } catch (UploadException $e) {
            $this->error($e->getMessage());
        }

        $this->success(__('Uploaded successful'), ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);
    }
}

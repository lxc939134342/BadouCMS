<?php

// +----------------------------------------------------------------------
// | BADOUCMS [ 八斗网站系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2024-2030 http://doc.ldcode.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: lande <939134342@qq.com>
// +----------------------------------------------------------------------

namespace app\common\library;

use think\File;
use badou\Random;
use app\common\model\Attachment;
use app\common\exception\UploadException;
use think\facade\Event;

/**
 * 文件上传类
 */
class Upload
{
    protected $config = [];

    protected $error = '';

    /**
     * @var File
     */
    protected $file = null;
    protected $fileInfo = null;

    public function __construct($file = null)
    {
        $this->config = config('upload');
        if ($file) {
            $this->setFile($file);
        }
    }

    /**
     * 获取文件
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * 设置文件
     * @param $file
     * @throws UploadException
     */
    public function setFile($file)
    {
        if (empty($file)) {
            throw new UploadException(__('No file upload or server upload limit exceeded'));
        }
        $suffix                  = strtolower($file->extension());
        $suffix                  = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        $fileInfo['suffix']      = $suffix;
        $fileInfo['type']        = $file->getMime();
        $fileInfo['size']        = $file->getSize();
        $fileInfo['name']        = $file->getOriginalName();
        $fileInfo['sha1']        = $file->sha1();
        $fileInfo['imagewidth']  = 0;
        $fileInfo['imageheight'] = 0;
        $fileInfo['tmp_name']    = $file->getPathname();

        $this->file = $file;
        $this->fileInfo = $fileInfo;
        $this->checkExecutable();
    }

    /**
     * 检测是否为可执行脚本
     * @return bool
     * @throws UploadException
     */
    protected function checkExecutable()
    {
        //禁止上传以.开头的文件
        if (substr($this->fileInfo['name'], 0, 1) === '.') {
            throw new UploadException(__('Uploaded file format is limited'));
        }

        //禁止上传PHP和HTML文件
        if (in_array($this->fileInfo['type'], ['text/x-php', 'text/html']) || in_array($this->fileInfo['suffix'], ['php', 'html', 'htm', 'phar', 'phtml']) || preg_match("/^php(.*)/i", $this->fileInfo['suffix'])) {
            throw new UploadException(__('Uploaded file format is limited'));
        }
        return true;
    }

    /**
     * 检测文件类型
     * @return bool
     * @throws UploadException
     */
    protected function checkMimetype()
    {
        $mimetypeArr = explode(',', strtolower($this->config['mimetype']));
        $typeArr = explode('/', $this->fileInfo['type']);
        //Mimetype值不正确
        if (stripos($this->fileInfo['type'], '/') === false) {
            throw new UploadException(__('Uploaded file format is limited'));
        }
        //验证文件后缀
        if (in_array($this->fileInfo['suffix'], $mimetypeArr) || in_array('.' . $this->fileInfo['suffix'], $mimetypeArr)
            || in_array($typeArr[0] . "/*", $mimetypeArr) || (in_array($this->fileInfo['type'], $mimetypeArr) && stripos($this->fileInfo['type'], '/') !== false)) {
            return true;
        }
        throw new UploadException(__('Uploaded file format is limited'));
    }

    /**
     * 检测是否图片
     * @param bool $force
     * @return bool
     * @throws UploadException
     */
    protected function checkImage($force = false)
    {
        //验证是否为图片文件
        if (in_array($this->fileInfo['type'], ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png', 'image/webp']) || in_array($this->fileInfo['suffix'], ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
            $imgInfo = getimagesize($this->fileInfo['tmp_name']);
            if (!$imgInfo || !isset($imgInfo[0]) || !isset($imgInfo[1])) {
                throw new UploadException(__('Uploaded file is not a valid image'));
            }
            $this->fileInfo['imagewidth'] = $imgInfo[0] ?? 0;
            $this->fileInfo['imageheight'] = $imgInfo[1] ?? 0;
            return true;
        } else {
            return !$force;
        }
    }

    /**
     * 检测文件大小
     * @throws UploadException
     */
    protected function checkSize()
    {
        preg_match('/([0-9\.]+)(\w+)/', $this->config['maxsize'], $matches);
        $size = $matches ? $matches[1] : $this->config['maxsize'];
        $type = $matches ? strtolower($matches[2]) : 'b';
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int)($size * pow(1024, $typeDict[$type] ?? 0));
        if ($this->fileInfo['size'] > $size) {
            throw new UploadException(__(
                'File is too big (%sMiB), Max filesize: %sMiB',
                [round($this->fileInfo['size'] / pow(1024, 2), 2),
                round($size / pow(1024, 2), 2)]
            ));
        }
    }

    /**
     * 获取后缀
     * @return string
     */
    public function getSuffix()
    {
        return $this->fileInfo['suffix'] ?: 'file';
    }

    /**
     * 获取存储的文件名
     * @param string $savekey  保存路径
     * @param string $filename 文件名
     * @param string $md5      文件MD5
     * @param string $category 分类
     * @return mixed|null
     */
    public function getSavekey($savekey = null, $filename = null, $md5 = null, $category = null)
    {
        if ($filename) {
            $suffix = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        } else {
            $suffix = $this->fileInfo['suffix'] ?? '';
        }
        $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        $filename = $filename ? $filename : ($this->fileInfo['name'] ?? 'unknown');
        $filename = xss_clean(strip_tags(htmlspecialchars($filename)));
        $fileprefix = substr($filename, 0, strripos($filename, '.'));
        $md5 = $md5 ? $md5 : (isset($this->fileInfo['tmp_name']) ? md5_file($this->fileInfo['tmp_name']) : '');
        $category = $category ? $category : request()->post('category');
        $category = $category ? xss_clean($category) : 'all';
        $replaceArr = [
            '{year}'       => date("Y"),
            '{mon}'        => date("m"),
            '{day}'        => date("d"),
            '{hour}'       => date("H"),
            '{min}'        => date("i"),
            '{sec}'        => date("s"),
            '{random}'     => Random::build('alnum', 16),
            '{random32}'   => Random::build('alnum', 32),
            '{category}'   => $category ? $category : '',
            '{filename}'   => substr($filename, 0, 100),
            '{fileprefix}' => substr($fileprefix, 0, 100),
            '{suffix}'     => $suffix,
            '{.suffix}'    => $suffix ? '.' . $suffix : '',
            '{filemd5}'    => $md5,
        ];
        $savekey = $savekey ? $savekey : $this->config['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        return $savekey;
    }

    /**
     * 普通上传
     * @return \app\common\model\attachment|\think\Model
     * @throws UploadException
     */
    public function upload($savekey = null)
    {
        if (empty($this->file)) {
            throw new UploadException(__('No file upload or server upload limit exceeded'));
        }

        $this->checkSize();
        $this->checkExecutable();
        $this->checkMimetype();
        $this->checkImage();

        $savekey = $savekey ? $savekey : $this->getSavekey();
        $savekey = '/' . ltrim($savekey, '/');
        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);

        $destDir = root_path() . 'public' . str_replace('/', DS, $uploadDir);

        $sha1 = $this->file->hash();

        $file = $this->file->move($destDir, $fileName);
        if (!$file) {
            // 上传失败获取错误信息
            throw new UploadException($this->file->getError());
        }

        $this->file = $file;
        $category = request()->post('category');
        $category = array_key_exists($category, config('site.attachmentcategory') ?? []) ? $category : '';
        $auth = AdminAuth::instance();
        $params = array(
            'admin_id'    => (int)session('admin.id'),
            'user_id'     => (int)$auth->id,
            'filename'    => mb_substr(htmlspecialchars(strip_tags($this->fileInfo['name'])), 0, 100),
            'category'    => $category,
            'filesize'    => $this->fileInfo['size'],
            'imagewidth'  => $this->fileInfo['imagewidth'],
            'imageheight' => $this->fileInfo['imageheight'],
            'imagetype'   => $this->fileInfo['suffix'],
            'imageframes' => 0,
            'mimetype'    => $this->fileInfo['type'],
            'url'         => $uploadDir . $fileName,
            'upload_time'  => time(),
            'storage'     => 'local',
            'sha1'        => $sha1,
            'extparam'    => '',
        );
        $attachment = new Attachment();
        $attachment->data(array_filter($params));
        $attachment->save();
        Event::trigger("upload_after", $attachment);
        return $attachment;
    }

    /**
     * 设置错误信息
     * @param $msg
     */
    public function setError($msg)
    {
        $this->error = $msg;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}

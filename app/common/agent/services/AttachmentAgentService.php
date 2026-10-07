<?php

namespace app\common\agent\services;

use app\common\model\Attachment;
use think\facade\Event;

class AttachmentAgentService
{
    public function search(string $keyword, int $limit = 20): array
    {
        $keyword = trim($keyword);
        $limit = max(1, min(20, $limit));
        $query = Attachment::field('id,category,admin_id,filename,filesize,mimetype,url,storage,create_time');
        if ($keyword !== '') {
            $query->where(function ($query) use ($keyword): void {
                if (ctype_digit($keyword)) {
                    $query->where('id', (int)$keyword);
                }
                $query->whereOr('filename', 'like', '%' . $keyword . '%')
                    ->whereOr('url', 'like', '%' . $keyword . '%');
            });
        }
        $rows = $query->order('id', 'desc')->limit($limit)->select()->toArray();
        foreach ($rows as &$row) {
            $row['filesize_text'] = format_bytes((int)$row['filesize']);
        }
        unset($row);
        return ['total' => count($rows), 'attachments' => $rows];
    }

    public function deletePreview(int $id): array
    {
        $row = $this->find($id);
        return [
            'requires_confirmation' => true,
            'deleted' => false,
            'message' => '尚未删除。请向用户确认后，再以 confirm=true 调用 attachment_delete。',
            'attachment' => $row,
        ];
    }

    public function readText(string $identifier, int $offset = 0, int $limit = 12000): array
    {
        $identifier = trim($identifier);
        $query = Attachment::field('id,filename,filesize,mimetype,url,storage');
        if (ctype_digit($identifier) && (int)$identifier > 0) {
            $row = $query->where('id', (int)$identifier)->find();
        } elseif (str_starts_with($identifier, '/uploads/')) {
            $row = $query->where('url', $identifier)->find();
        } else {
            throw new \DomainException('请使用已上传附件的 ID 或 /uploads/ 开头的附件地址');
        }
        if (!$row) throw new \DomainException('附件不存在，请先上传文件或查询附件');
        return (new WordAttachmentReader())->read($row->getData(), $offset, $limit);
    }

    public function delete(int $id): array
    {
        $attachment = Attachment::find($id);
        if (!$attachment) {
            throw new \DomainException('附件不存在');
        }
        $snapshot = $this->find($id);
        Event::trigger('upload_delete', $attachment);
        if ($attachment->delete() === false) {
            throw new \RuntimeException('附件删除失败');
        }
        return ['deleted' => true, 'attachment' => $snapshot];
    }

    private function find(int $id): array
    {
        $row = Attachment::field('id,filename,filesize,mimetype,url,storage')->find($id);
        if (!$row) {
            throw new \DomainException('附件不存在');
        }
        $data = $row->toArray();
        $data['filesize_text'] = format_bytes((int)$data['filesize']);
        return $data;
    }
}

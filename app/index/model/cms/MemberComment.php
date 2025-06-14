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

namespace app\index\model\cms;

use think\Model;

class MemberComment extends Model
{
    protected $name = 'cms_member_comment';
    protected $autoWriteTimestamp = true;

    protected $append = [
        'date',
        'replyaction',
        'headpic',
        'status_text'
    ];

    public function getDateAttr($value, $data): string
    {
        return $data['create_time'];
    }

    public function getReplyactionAttr($value, $data): string
    {
        if ($data['pid']) {
            $pid = $data['pid'];
        } else {
            $pid = $data['id'];
        }
        return url('/comment/add', ['contentid' => $data['contentid'],'pid' => $pid,'puid' => $data['uid']]);
    }

    public function getHeadpicAttr($value, $data): string
    {
        return $data['avatar'] ?? '';
    }

    public function getStatusTextAttr($value, $data)
    {
        return $data['status'] == 1 ? __('show') : __('hide');
    }

    public function getCommentAttr($value, $data)
    {
        if (empty($data['comment'])) {
            return '';
        }

        // 多重解码，处理可能多次编码的情况
        $comment = $data['comment'];
        $comment = html_entity_decode($comment, ENT_QUOTES, 'UTF-8');
        $comment = htmlspecialchars_decode($comment, ENT_QUOTES);

        return $comment;
    }

    /**
     * 内容
     * @return \think\model\relation\BelongsTo
     */
    public function content()
    {
        return $this->belongsTo(Content::class, 'contentid', 'id');
    }


    // 文章评论
    public function getComment($contentid, $pid, $num, $order, $page = false, $start = 1)
    {
        $simple = false;//简洁分页
        $field = array(
            'a.*',
            'b.username',
            'b.nickname',
            'b.avatar',
            'c.username as pusername',
            'c.nickname as pnickname',
            'c.avatar as pavatar'
        );
        $where = [
            "a.contentid" => $contentid,
            "a.pid" => $pid,
            "a.status" => 1
        ];
        $data = [
            'total' => 0,
            'data' => [],
            'page' => [
                'index'   => '',
                'pre'     => '',
                'next'    => '',
                'last'    => '',
                'bar'     => '',
                'current' => '',
                'count'   => '',
                'rows'    => '',
                'number'  => ''
            ]
        ];

        if ($page) {
            $db = $this->alias('a')->field($field)
                ->join('user b', 'a.uid=b.id', 'LEFT')
                ->join('user c', 'a.puid=c.id', 'LEFT')
                ->where($where)
                ->order($order)
                ->limit($start - 1);
            $getparam = request()->get();
            unset($getparam['captcha']);
            $res = $db->paginate([
            'query' => $getparam,
                'list_rows' => $num,
            ], $simple);

            if (!$res->isEmpty()) {
                $data['total'] = $res->total();
                $data['per_page'] = $res->listRows();
                $data['current_page'] = $res->currentPage();
                $data['last_page'] = $res->lastPage();
                $data['data'] = $res->getCollection()->toArray();
                $data['page'] = $res->pageData();
            }
        } else {
            $db = $this->alias('a')->field($field)
                ->join('user b', 'a.uid=b.id', 'LEFT')
                ->join('user c', 'a.puid=c.id', 'LEFT')
                ->where($where)
                ->order($order);

            $res = $db->limit($start, $num)->select();
            if (!$res->isEmpty()) {
                $data['total'] = $res->count();
                $data['data'] = $res->toArray();
            }
        }

        return $data;
    }

}

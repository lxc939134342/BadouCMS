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

namespace app\admin\model\cms;

use think\Model;
use app\admin\model\User;

/**
 * MemberComment
 */
class MemberComment extends Model
{
    // 表名
    protected $name = 'cms_member_comment';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    protected $append = ['status_text'];

    /**
     * 内容
     * @return \think\model\relation\BelongsTo
     */
    public function content(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(Content::class, 'contentid', 'id')->field('id,title');
    }

    /**
     * 用户
     * @return \think\model\relation\BelongsTo
     */
    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(User::class, 'uid', 'id')->field('id,nickname');
    }

    /**
     * 被评论人
     * @return \think\model\relation\BelongsTo
     */
    public function puser(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(User::class, 'puid', 'id')->field('id,nickname');
    }

    public function getStatusTextAttr($value, $data)
    {
        return $data['status'] == 1 ? __('show') : __('hide');
    }


}

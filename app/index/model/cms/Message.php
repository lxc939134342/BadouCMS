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

class Message extends Model
{
    protected $name = 'cms_message';
    protected $autoWriteTimestamp = true;

    protected $append = ['replydate', 'ip','os','bs','askdate','headpic'];

    public function getReplydateAttr($value, $data)
    {
        return $data['update_time'];
    }

    public function getIpAttr($value, $data)
    {
        return $data['user_ip'];
    }

    public function getOsAttr($value, $data)
    {
        return $data['user_os'];
    }

    public function getBsAttr($value, $data)
    {
        return $data['user_bs'];
    }

    public function getAskdateAttr($value, $data)
    {
        return $data['create_time'];
    }

    public function getHeadpicAttr($value, $data)
    {
        return $data['avatar'] ?? '';
    }

    public function getNicknameAttr($value, $data)
    {
        return $data['username'] ?? $data['nickname'] ?? __('Anonymous user');
    }

    public function getList($num, $order, $page = false, $start = 1)
    {
        $simple = false;//简洁分页
        $where = [
            "a.status" => 1,
            'a.acode' => get_frontend_lang()
        ];

        $field = array(
            'a.*',
            'b.username',
            'b.nickname',
            'b.avatar'
        );
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
            $db = $this->alias('a')
                ->field($field)
                ->join('user b', 'a.uid=b.id', 'LEFT')
                ->where($where)
                ->order($order)
                ->limit($start - 1);

            $getparam = request()->get();
            unset($getparam['captcha']);

            $res = $db->paginate([
                'query'     => $getparam,
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
            $db = $this->alias('a')
                ->field($field)
                ->join('user b', 'a.uid=b.id', 'LEFT')
                ->where($where)
                ->order($order)
                ->limit($start - 1);

            $res = $db->limit($num)->select();
            if (!$res->isEmpty()) {
                $data['total'] = $res->count();
                $data['data'] = $res->toArray();
            }
        }

        return $data;
    }
}

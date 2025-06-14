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
use think\facade\Db;

class Form extends Model
{
    protected $name = 'cms_form';

    protected $autoWriteTimestamp = true;

    // 获取表单字段
    public function getFormField($fcode)
    {
        $field = array(
            'a.table_name',
            'a.form_name',
            'b.name',
            'b.required',
            'b.description'
        );

        $result = $this->alias('a')
            ->field($field)
            ->where('a.fcode', $fcode)
            ->join(
                'cms_form_field b',
                'a.fcode=b.fcode',
                'LEFT'
            )
            ->order('b.sorting ASC,b.id ASC')
            ->select();
        if ($result->isEmpty()) {
            return [];
        }
        return $result->toArray();
    }

    // 添加表单数据
    public function addTableData($fcode, $data)
    {
        $table_name = $this->where('fcode', $fcode)->value('table_name');
        return Db::table($table_name)->insert($data) > 0 ? true : false;
    }

    // 获取表单列表
    public function getFormList($fcode, $num, $order = 'id DESC', $page = false, $start = 1)
    {
        $table_name = $this->where('fcode', $fcode)->value('table_name');
        $simple = false;//简洁分页
        $where = [
            'acode' => get_frontend_lang()
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
            $db = Db::table($table_name)
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
                $beforeData = $res->getCollection()->toArray();
                $data['data'] = $this->eachFields($beforeData);
                $data['page'] = $res->pageData();
            }
        } else {
            $db = Db::table($table_name)
                ->where($where)
                ->order($order)
                ->limit($start - 1);

            $res = $db->limit($num)->select();
            if (!$res->isEmpty()) {
                $data['total'] = $res->count();
                $beforeData =  $res->toArray();
                $data['data'] = $this->eachFields($beforeData);
            }
        }

        return $data;
    }

    /**
     * 过滤数据
     * @param mixed $data
     * @return mixed
     */
    protected function eachFields($data)
    {

        $afterData = [];
        foreach ($data as $key => $value) {
            if ($value['create_time']) {
                $value['date'] = $value['create_time'];
            }
            $afterData[] = $value;
        }
        return $afterData;
    }

    // 获取表单链接
    public function getFormLink(int $fcode)
    {
        return url('/message/submit_form', ['fcode' => $fcode])->build();
    }

}

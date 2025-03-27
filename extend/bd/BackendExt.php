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

namespace bd;

trait BackendExt
{
    public function sorting(int $id, int $sorting): void
    {
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $this->model->where($this->dataLimitField, 'in', $dataLimitAdminIds);
        }

        $row    = $this->model->find($id);

        if (!$row) {
            $this->error(__('Record not found'));
        }
        if ($row[$this->weighField]) {
            $row[$this->weighField]   = $sorting;
            $row->save();
        }

        $this->success();
    }
}

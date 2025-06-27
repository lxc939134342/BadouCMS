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

namespace app\common\traits;

use app\admin\library\Auth;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Throwable;
use PDOException;
use think\Exception;
use think\facade\Db;

trait Backend
{
    /**
     * 排除入库字段
     * @param array $params
     * @return array
     */
    protected function preExcludeFields($params)
    {
        if (!is_array($this->excludeFields)) {
            $this->excludeFields = explode(',', (string)$this->excludeFields);
        }

        foreach ($this->excludeFields as $field) {
            if (array_key_exists($field, $params)) {
                unset($params[$field]);
            }
        }
        return $params;
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index()
    {
        if (!$this->isAjax()) {
            return $this->view->fetch();
        }

        if (!$this->model) {
            $this->error(__('Please configure the model first'));
        }

        if ($this->request->param('select')) {
            $this->select();
        }

        [$where, $sort, $order, $offset, $limit] = $this->buildparams();

        $res = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);

        $this->result('', $res->items(), $res->total());
    }

    /**
     * 添加
     */
    public function add()
    {
        if (!$this->isAjax()) {
            return $this->view->fetch();
        }
        $data = $this->request->post('row/a');
        if (!$data) {
            $this->error(__('Parameter %s can not be empty', ['']));
        }

        $data = $this->preExcludeFields($data);
        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $data[$this->dataLimitField] = $this->auth->id;
        }

        $result = false;
        $this->model->startTrans();
        try {
            // 模型验证
            if ($this->modelValidate) {
                $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                if (class_exists($validate)) {
                    $validate = new $validate();
                    if ($this->modelSceneValidate) {
                        $validate->scene('add');
                    }
                    $validate->check($data);
                }
            }
            $result = $this->model->save($data);
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success(__('Add successful'));
    }

    /**
     * 编辑
     * @throws Throwable
     */
    public function edit()
    {
        $id  = $this->request->param('ids');
        $row = $this->model->where($this->pk, 'in', $id)->find();
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post('row/a');
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data   = $this->preExcludeFields($data);
            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) {
                            $validate->scene('edit');
                        }
                        $validate->check($data);
                    }
                }
                $result = $row->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

    /**
     * 删除
     * @throws Throwable
     */
    public function del()
    {
        $where             = [];
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$this->dataLimitField, 'in', $dataLimitAdminIds];
        }

        $ids     = $this->request->param('ids');
        $where[] = [$this->pk, 'in', $ids];
        $data    = $this->model->where($where)->select();

        $count = 0;
        $this->model->startTrans();
        try {
            foreach ($data as $v) {
                $count += $v->delete();
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success(__('Delete successful'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }

    /**
     * 批量更新
     *
     * @param $ids
     * @return void
     */
    public function multi()
    {
        if (false === $this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', ['ids']));
        }

        if (false === $this->request->has('params')) {
            $this->error(__('No rows were updated'));
        }
        parse_str($this->request->post('params'), $values);
        $values = $this->auth->isSuperAdmin() ? $values : array_intersect_key($values, array_flip(is_array($this->multiFields) ? $this->multiFields : explode(',', $this->multiFields)));
        if (empty($values)) {
            $this->error(__('You have no permission'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $count = 0;
        Db::startTrans();
        try {
            $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
            foreach ($list as $item) {
                $count += $item->allowField([])->save($values);
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were updated'));
    }

    /**
     * 排序 - 增量重排法
     * @throws Throwable
     */
    public function sortable()
    {
        $pk        = $this->model->getPk();
        $move      = $this->request->param('move');
        $target    = $this->request->param('target');
        $order     = $this->request->param("order/s") ?: $this->defaultSortField;
        $direction = $this->request->param('direction');

        $dataLimitWhere    = [];
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $dataLimitWhere[] = [$this->dataLimitField, 'in', $dataLimitAdminIds];
        }

        $moveRow   = $this->model->where($dataLimitWhere)->find($move);
        $targetRow = $this->model->where($dataLimitWhere)->find($target);

        if ($move == $target || !$moveRow || !$targetRow || !$direction) {
            $this->error(__('Record not found'));
        }

        // 当前是否以权重字段排序（只检查当前排序和默认排序字段，不检查有序保证字段）
        if ($order && is_string($order)) {
            $order = explode(',', $order);
            $order = [$order[0] => $order[1] ?? 'asc'];
        }
        if (!array_key_exists($this->weighField, $order)) {
            $this->error(__('Please use the %s field to sort before operating', [$this->weighField]));
        }

        // 开始增量重排
        $order = $this->queryOrderBuilder();
        $weigh = $targetRow[$this->weighField];

        // 波及行的权重值向上增加还是向下减少
        if ($order[$this->weighField] == 'desc') {
            $updateMethod = $direction == 'up' ? 'dec' : 'inc';
        } else {
            $updateMethod = $direction == 'up' ? 'inc' : 'dec';
        }

        // 与目标行权重相同的行
        $weighRowIds    = $this->model
            ->where($dataLimitWhere)
            ->where($this->weighField, $weigh)
            ->order($order)
            ->column($pk);
        $weighRowsCount = count($weighRowIds);

        // 单个 SQL 查询中完成大于目标权重行的修改
        $this->model->where($dataLimitWhere)
            ->where($this->weighField, $updateMethod == 'dec' ? '<' : '>', $weigh)
            ->whereNotIn($pk, [$moveRow->$pk])
            ->$updateMethod($this->weighField, $weighRowsCount)
            ->save();

        // 遍历与目标行权重相同的行，每出现一行权重值将额外 +1，保证权重相同行的顺序位置不变
        if ($direction == 'down') {
            $weighRowIds = array_reverse($weighRowIds);
        }

        $moveComplete = 0;
        $weighRowIds  = implode(',', $weighRowIds);
        $weighRows    = $this->model->where($dataLimitWhere)
            ->where($pk, 'in', $weighRowIds)
            ->orderRaw("field($pk,$weighRowIds)")
            ->select();

        // 权重相等行
        foreach ($weighRows as $key => $weighRow) {
            // 跳过当前拖动行（相等权重数据之间的拖动时，被拖动行会出现在 $weighRows 内）
            if ($moveRow[$pk] == $weighRow[$pk]) {
                continue;
            }

            if ($updateMethod == 'dec') {
                $rowWeighVal = $weighRow[$this->weighField] - $key;
            } else {
                $rowWeighVal = $weighRow[$this->weighField] + $key;
            }

            // 找到了目标行
            if ($weighRow[$pk] == $targetRow[$pk]) {
                $moveComplete               = 1;
                $moveRow[$this->weighField] = $rowWeighVal;
                $moveRow->save();
            }

            $rowWeighVal                 = $updateMethod == 'dec' ? $rowWeighVal - $moveComplete : $rowWeighVal + $moveComplete;
            $weighRow[$this->weighField] = $rowWeighVal;
            $weighRow->save();
        }

        $this->success();
    }

    /**
     * 加载为select(远程下拉选择框)数据，默认还是走$this->index()方法
     * 必要时请在对应控制器类中重写
     */
    public function select()
    {

    }

    /**
     ** 导入
     ** | Author: Wusn <958342972@qq.com>
     ** | @return void
     ** * @throws \Exception
     ** | DateTime: 2025/6/14 16:22
     */
    protected function import()
    {
        $file = $this->request->request('file');
        if (!$file) {
            $this->error(__('Parameter %s can not be empty', 'file'));
        }
        $filePath = public_path() . DS . $file;
        if (!is_file($filePath)) {
            $this->error(__('No results were found'));
        }
        //实例化reader
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            $this->error(__('Unknown data format'));
        }
        if ($ext === 'csv') {
            $file = fopen($filePath, 'r');
            $filePath = tempnam(sys_get_temp_dir(), 'import_csv');
            $fp = fopen($filePath, 'w');
            $n = 0;
            while ($line = fgets($file)) {
                $line = rtrim($line, "\n\r\0");
                $encoding = mb_detect_encoding($line, ['utf-8', 'gbk', 'latin1', 'big5']);
                if ($encoding !== 'utf-8') {
                    $line = mb_convert_encoding($line, 'utf-8', $encoding);
                }
                if ($n == 0 || preg_match('/^".*"$/', $line)) {
                    fwrite($fp, $line . "\n");
                } else {
                    fwrite($fp, '"' . str_replace(['"', ','], ['""', '","'], $line) . "\"\n");
                }
                $n++;
            }
            fclose($file) || fclose($fp);

            $reader = new Csv();
        } elseif ($ext === 'xls') {
            $reader = new Xls();
        } else {
            $reader = new Xlsx();
        }

        //导入文件首行类型,默认是注释,如果需要使用字段名称请使用name
        $importHeadType = isset($this->importHeadType) ? $this->importHeadType : 'comment';

        $table = $this->model->getQuery()->getTable();
        $database = \think\Config::get('database.database');
        $fieldArr = [];
        $list = db()->query("SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$table, $database]);
        foreach ($list as $k => $v) {
            if ($importHeadType == 'comment') {
                $v['COLUMN_COMMENT'] = explode(':', $v['COLUMN_COMMENT'])[0]; //字段备注有:时截取
                $fieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];
            } else {
                $fieldArr[$v['COLUMN_NAME']] = $v['COLUMN_NAME'];
            }
        }

        //加载文件
        $insert = [];
        try {
            if (!$PHPExcel = $reader->load($filePath)) {
                $this->error(__('Unknown data format'));
            }
            $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表
            $allColumn = $currentSheet->getHighestDataColumn(); //取得最大的列号
            $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
            $maxColumnNumber = Coordinate::columnIndexFromString($allColumn);
            $fields = [];
            for ($currentRow = 1; $currentRow <= 1; $currentRow++) {
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $fields[] = $val;
                }
            }

            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                $values = [];
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $values[] = is_null($val) ? '' : $val;
                }
                $row = [];
                $temp = array_combine($fields, $values);
                foreach ($temp as $k => $v) {
                    if (isset($fieldArr[$k]) && $k !== '') {
                        $row[$fieldArr[$k]] = $v;
                    }
                }
                if ($row) {
                    $insert[] = $row;
                }
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
        if (!$insert) {
            $this->error(__('No rows were updated'));
        }

        try {
            //是否包含admin_id字段
            $has_admin_id = false;
            foreach ($fieldArr as $name => $key) {
                if ($key == 'admin_id') {
                    $has_admin_id = true;
                    break;
                }
            }
            if ($has_admin_id) {
                $auth = Auth::instance();
                foreach ($insert as &$val) {
                    if (empty($val['admin_id'])) {
                        $val['admin_id'] = $auth->isLogin() ? $auth->id : 0;
                    }
                }
            }
            $this->model->saveAll($insert);
        } catch (\think\db\exception\PDOException $exception) {
            $msg = $exception->getMessage();
            if (preg_match("/.+Integrity constraint violation: 1062 Duplicate entry '(.+)' for key '(.+)'/is", $msg, $matches)) {
                $msg = "导入失败，包含【{$matches[1]}】的记录已存在";
            };
            $this->error($msg);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success();
    }
}

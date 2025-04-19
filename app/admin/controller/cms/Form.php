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

namespace app\admin\controller\cms;

use ba\Exception;
use ba\TableManager;
use think\db\exception\PDOException;
use think\facade\Db;
use Throwable;
use app\admin\library\crud\Helper;

/**
 * 自定义表单
 */
class Form extends Base
{
    /**
     * Form模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\Form
     */
    protected object $model;

    /**
     * FormField模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\FormField
     */
    protected object $formFieldModel;


    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected string|array $quickSearchField = ['id','form_name'];


    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Form();
        $this->formFieldModel = new \app\admin\model\cms\FormField();
    }

    /**
     * 添加
     * @return void
     */
    public function add(): void
    {
        $formModel = $this->model;
        if ($this->request->isPost()) {
            $post = $this->getOriginalInputData();

            $post['create_user'] = $this->auth->username;
            $post['update_user'] = $this->auth->username;

            if (empty($this->request->post('fcode'))) {
                $fcode = $formModel->order('fcode', 'desc')->value('fcode');
                $post['fcode'] = $fcode + 1;
            }
            // 获取数据库配置的表前缀
            $dbPrefix = config('database.connections.mysql.prefix').'cms_diy_';
            $table_name = $this->request->post('table_name');
            if (strpos($table_name, $dbPrefix) !== 0) {
                $table_name = $dbPrefix . $table_name;
            }
            $post['table_name'] = $table_name;
            $this->request->withPost($post);

            $table = [
                "name" => $table_name,
                "comment" => $this->request->post('form_name'),
                "isCommonModel" => 0,
                "databaseConnection" => 'mysql',
                "rebuild" => 'Yes',
            ];
            $fields  = [
                [
                    "name" => "id",
                    "type" => "int",
                    "dataType" => "int(10)",
                    "default" => "",
                    "defaultType" => "NONE",
                    "null" => false,
                    "primaryKey" => true,
                    "unsigned" => true,
                    "autoIncrement" => true,
                    "comment" => "",
                    "designType" => "pk",
                ],
                [
                    "name" => "acode",
                    "type" => "varchar",
                    "dataType" => "varchar(20)",
                    "default" => "",
                    "defaultType" => "NONE",
                    "null" => false,
                    "primaryKey" => false,
                    "unsigned" => false,
                    "autoIncrement" => false,
                    "comment" => "区域编码",
                    "designType" => "string",
                ],
                [
                    "name" => "create_time",
                    "type" => "datetime",
                    "dataType" => "datetime",
                    "default" => "",
                    "defaultType" => "NONE",
                    "null" => false,
                    "primaryKey" => false,
                    "unsigned" => false,
                    "autoIncrement" => false,
                    "comment" => "",
                    "designType" => "timestamp",
                ],
            ];

            try {
                Helper::handleTableDesign($table, $fields);
            } catch (Exception $e) {
                $this->error($e->getMessage());
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }

            parent::add();
        }

    }


    /**
     * 删除
     * @param array $ids
     * @throws Throwable
     */
    public function del(array $ids = []): void
    {
        if (!$this->request->isDelete() || !$ids) {
            $this->error(__('Parameter error'));
        }

        $where             = [];
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$this->dataLimitField, 'in', $dataLimitAdminIds];
        }

        $pk      = $this->model->getPk();
        $where[] = [$pk, 'in', $ids];

        $count = 0;
        $data  = $this->model->where($where)->select();
        $this->model->startTrans();
        try {
            foreach ($data as $v) {

                $count += $v->delete();

                $tempLine = 'drop table '.$v['table_name'].';';
                try {
                    Db::execute($tempLine);
                } catch (PDOException) {
                    // $e->getMessage();
                }

                //删除对应 表 多个字段
                $this->formFieldModel->where('fcode', $v['fcode'])->delete();
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success(__('Deleted successfully'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }


}

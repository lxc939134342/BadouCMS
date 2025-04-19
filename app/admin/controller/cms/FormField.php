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

use app\admin\library\crud\Helper;
use ba\Exception;
use Throwable;

/**
 * 自定义表单-字段
 */
class FormField extends Base
{
    /**
     * Field模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\FormField
     */
    protected object $model;

    protected object $formModel;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected string|array $quickSearchField = ['id','name'];
    protected int $fcode;

    protected array $noNeedPermission = ['getFormFieldList'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\FormField();
        $this->formModel = new \app\admin\model\cms\Form();
        $this->fcode = $this->request->param('fcode', 1);
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        if ($this->fcode) {
            $where[] = [
                'fcode', '=', $this->fcode
            ];
        }
        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->select();
        $this->success('', [
            'list'   => $res,
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function getFormFieldList(): void
    {
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $where[] = [
            'fcode','=',$this->fcode
        ];

        if (empty($order)) {
            $order = 'sorting ASC';
        } else {
            $order = 'id ASC';
        }

        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->select()->toArray();

        $this->success('', [
            'list'   => $res,
            'remark' => get_route_remark(),
        ]);
    }



    /**
     * 添加
     * @return void
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $post = $this->getOriginalInputData();
            $post['acode'] = get_backend_lang();
            $post['create_user'] = $this->auth->username;
            $post['update_user'] = $this->auth->username;

            if (empty($this->request->post('fcode'))) {
                $formFieldModel = $this->model;
                $fcode = $formFieldModel->order('fcode', 'desc')->value('fcode');
                $post['fcode'] = $fcode + 1;
            }
            $this->request->withPost($post);
            //重构，采用调用 Helper::handleTableDesign 方式创建 表 对应字段

            $formModel = new \app\admin\model\cms\Form();
            $form = $formModel->where('fcode', $post['fcode'])->find()->toArray();
            $table = [
                "name" => $form['table_name'],
                "comment" => $form['form_name'],
                "isCommonModel" => 0,
                "databaseConnection" => 'mysql',
                "rebuild" => 'Yes',
                "designChange" => [
                    [
                        "index" => 0,
                        "type" => "add-field",
                        "oldName" => '',
                        "newName" => $post['name'],
                        "sync" => true,
                    ]
                ]
            ];
            $fields  = [
                [
                    "name" => $post['name'],
                    "type" => "varchar",
                    "dataType" => "varchar(".$post['length'].")",
                    "default" => "",
                    "defaultType" => "NONE",
                    "null" => false,
                    "primaryKey" => false,
                    "unsigned" => false,
                    "autoIncrement" => false,
                    "comment" => $post['description'],
                    "designType" => "string",
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

                $formModel = new \app\admin\model\cms\Form();
                $form = $formModel->where('fcode', $v['fcode'])->find()->toArray();
                //重构，采用调用 Helper::handleTableDesign 方式删除 字段
                $table = [
                    "name" => $form['table_name'],
                    "comment" => $form['form_name'],
                    "isCommonModel" => 0,
                    "databaseConnection" => 'mysql',
                    "rebuild" => 'Yes',
                    "designChange" => [
                        [
                            "type" => "del-field",
                            "oldName" => $v['name'],
                            "newName" => '',
                            "sync" => true,
                        ]
                    ]
                ];
                $fields  = [
                    [
                        "name" => "test2",
                        "type" => "varchar",
                        "dataType" => "varchar(50)",
                        "default" => "",
                        "defaultType" => "NONE",
                        "null" => false,
                        "primaryKey" => false,
                        "unsigned" => false,
                        "autoIncrement" => false,
                        "comment" => "删除某一个字段",
                        "designType" => "string",
                    ],
                ];

                try {
                    Helper::handleTableDesign($table, $fields);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                } catch (Throwable $e) {
                    $this->error($e->getMessage());
                }

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

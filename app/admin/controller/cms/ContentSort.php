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

use ba\Tree;
use Throwable;
use think\facade\Db;
use app\admin\model\cms\Models;
use ba\Filesystem;

/**
 * 栏目管理
 */
class ContentSort extends Base
{
    /**
     * ContentSort模型对象
     * @var \app\admin\model\cms\ContentSort
     * @phpstan-var \app\admin\model\cms\ContentSort
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected string|array $quickSearchField = ['id','name'];
    protected string $weighField = "sorting";
    protected array $noNeedPermission = ['models','getTpls'];
    protected array $withJoinTable = ['models'];
    protected string $pk = 'scode';

    protected bool $modelValidate = true;
    /* 设置默认排序 */
    protected array|string $defaultSortField = [
        'pcode' => 'asc',
        'sorting' => 'asc',
        'id' => 'desc'
    ];

    protected object $tree;
    public function initialize(): void
    {
        parent::initialize();
        $this->tree  = Tree::instance();
        $this->model = new \app\admin\model\cms\ContentSort();

        $this->rules = [
            'name|'.__('name') => 'require',
            'mcode|'.__('mcode') => 'require',
        ];
    }

    public function index(): void
    {
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $istop = $this->request->param('istop/d', 1);

        $where[] = [
            'acode','=',get_backend_lang()
        ];
        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->select()->toArray();
        /**
         * 树状表格必看注释一
         * 1. 获取表格数据（没有分页，所以简化了以上的数据查询代码）
         * 2. 递归的根据指定字段组装 children 数组，此时直接给前端，表格就可以正常的渲染为树状了，一个方法搞定
         */
        $res = $this->tree->assembleChild($res, 'pcode', 'scode');

        if ($this->request->param('select')) {
            /**
             * 树状表格必看注释二
             * 1. 在远程 select 中，数据要成树状显示，需要对数据做一些改动
             * 2. 通过已组装好 children 的数据，建立`树枝`结构，并最终合并为一个二维数组方便渲染
             * 3. 简单讲就是把组装好 children 的数据，给以下这两个方法即可
             */
            $res = $this->tree->assembleTree($this->tree->getTreeArray($res));
            if ($istop) {
                array_unshift($res, ['id' => 0, 'name' => '顶级栏目','scode' => 0]);
            }
        }


        $this->success('', [
            'list'   => $res,
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 获取模型
     * @return void
     */
    public function models(): void
    {
        $modelsModel = new Models();
        $res = $modelsModel->where('status', 1)->order('id', 'desc')->select();
        $this->success('', [
            'list'   => $res,
        ]);
    }

    /**
     * 获取模版文件列表
     * @return void
     */
    public function getTpls(): void
    {
        $acode = get_backend_lang();
        $template = Db::name('cms_site')->where('acode', $acode)->value('theme');
        $path = root_path().'template'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            $this->error('template/'.$template.'模版目录不存在');
        }

        $files = Filesystem::getDirFiles($path, ['html']);
        $list = [];
        foreach ($files as $key => $value) {
            $list[] = ['id' => $key,'name' => $value];
        }
        $this->success('', [
            'list'   => $list,
        ]);
    }

    /**
     * 添加
     * @return void
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->getPostData();
            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                $this->modelValidateFunction($data);
                $lastcode = $this->model->getLastCode();
                $scode = get_auto_code($lastcode);
                $default = [
                    'acode'       => get_backend_lang(),
                    'pcode'       => 0,
                    'scode'       => $scode,
                    'name'        => '',
                    'mcode'       => 0,
                    'listtpl'     => '',
                    'contenttpl'  => '',
                    'status'      => 1,
                    'gid'         => 0,
                    'gtype'       => 4,
                    'subname'     => '',
                    'filename'    => '',
                    'outlink'     => '',
                    'ico'         => '',
                    'pic'         => '',
                    'title'       => '',
                    'keywords'    => '',
                    'description' => '',
                    'sorting'     => 255,
                    'create_user' => $this->auth->username,
                    'update_user' => $this->auth->username,
                    'def1' => '',
                    'def2' => '',
                    'def3' => '',
                ];

                $data = array_merge($default, $data);

                $result = $this->model->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 批量添加
     * @return void
     */
    public function batchAdd(): void
    {
        if ($this->request->isPost()) {
            $data = $this->getPostData();
            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                $this->modelValidateFunction($data);
                $multiplename = str_replace('，', ',', $data['name']);
                $names = explode(',', $multiplename);

                $lastcode = $this->model->getLastCode();
                $scode = get_auto_code($lastcode);
                $datalist = [];
                foreach ($names as $key => $value) {
                    $datalist[] = array(
                        'acode' => get_backend_lang(),
                        'pcode' => $data['pcode'] ?? 0,
                        'scode' => $scode,
                        'name' => $value,
                        'mcode' => $data['mcode'],
                        'listtpl' => $data['listtpl'],
                        'contenttpl' => $data['contenttpl'],
                        'status' => $data['status'],
                        'gid' => 0,
                        'gtype' => 4,
                        'subname' => '',
                        'filename' => '',
                        'outlink' => '',
                        'ico' => '',
                        'pic' => '',
                        'title' => '',
                        'keywords' => '',
                        'description' => '',
                        'sorting' => 255,
                        'create_user' => '',
                        'update_user' => '',
                        'def1' => '',
                        'def2' => '',
                        'def3' => '',
                    );
                    $scode = get_auto_code($scode);
                }
                $result = $this->model->saveAll($datalist);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }
}

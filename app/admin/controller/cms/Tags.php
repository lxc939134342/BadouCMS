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

/**
 * 文章内链
 */
class Tags extends Base
{
    /**
     * Tags模型对象
     * @var  \app\admin\model\cms\Tags
     */
    protected $model;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Tags();
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
    public function index()
    {
        if (!$this->request->isAjax()) {
            $this->assignHook('index');
            return $this->view->fetch();
        }

        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $sort, $order, $offset, $limit, $page, $alias, $bind) = $this->buildparams();
        $where[] = [
            'acode','=',get_backend_lang()
        ];

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
        if ($this->request->isPost()) {
            $post = $this->getOriginalInputData('row/a');
            $post['acode'] = get_backend_lang();
            $post['create_user'] = $this->auth->username;
            $post['update_user'] = $this->auth->username;

            $result = false;
            $this->model->startTrans();
            try {
                $context = new \badou\EventContext($post);
                $this->triggerObserver('BeforeAdd', $context, $this);
                if ($context->isIntercepted()) {
                    $this->error($context->getMessage());
                }
                $post = $context->getData();

                $result = $this->model->save($post);
                $this->triggerObserver('AfterAdd', $post, $this);
                $this->model->commit();
            } catch (\Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }

            if ($result !== false) {
                $this->success(__('Add successful'));
            } else {
                $this->error(__('No rows were added'));
            }
        }
        $this->assignHook('add', ['main_top', 'main_mid', 'main_bottom', 'side_top', 'side_bottom', 'footer', 'scripts'], []);
        return $this->view->fetch();
    }

    public function edit()
    {
        $ids = $this->request->param('ids');
        $row = $this->model->find($ids);
        if (!$row) {
            $this->error(__('Record not found'));
        }
        
        if ($this->request->isPost()) {
            $post = $this->getPostData('row/a', true);
            
            $result = false;
            $this->model->startTrans();
            try {
                $context = new \badou\EventContext($post, ['row' => $row]);
                $this->triggerObserver('BeforeEdit', $context, $this);
                if ($context->isIntercepted()) {
                    $this->error($context->getMessage());
                }
                $post = $context->getData();

                $result = $row->save($post);
                $this->triggerObserver('AfterEdit', $row, $post, $this);
                $this->model->commit();
            } catch (\Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }

            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }
        $this->assign('row', $row);
        $this->assignHook('edit', ['main_top', 'main_mid', 'main_bottom', 'side_top', 'side_bottom', 'footer', 'scripts'], $row->toArray());
        return $this->view->fetch();
    }

    public function del($ids = '')
    {
        $ids = $this->request->param('ids');
        if (!$ids) {
            $this->error(__('Parameter error'));
        }

        $res = $this->triggerObserver('BeforeDel', $ids, $this);
        if (is_array($res)) {
            $ids = $res;
        }

        $pk = $this->model->getPk();
        $list = $this->model->where($pk, 'in', $ids)->select();

        $count = 0;
        $this->model->startTrans();
        try {
            foreach ($list as $k => $v) {
                $count += $v->delete();
            }
            $this->model->commit();
        } catch (\Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }

        if ($count) {
            $this->success(__('Delete successful'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }

}

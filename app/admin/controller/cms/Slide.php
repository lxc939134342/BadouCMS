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

use app\admin\model\cms\Area as CmsArea;

/**
 * 轮播图片
 */
class Slide extends Base
{
    /**
     * Slide模型对象
     * @var \app\admin\model\cms\Slide
     */
    protected $model;


    protected $multiFields = 'sorting';

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Slide();
        $this->assign('gidList', $this->model->getGidList(get_backend_lang()));
    }

    public function index()
    {
        if (!$this->request->isAjax()) {
            $this->assignHook('index', []);
            return $this->view->fetch();
        }

        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $sort, $order, $offset, $limit, $page, $alias, $bind) = $this->buildparams();
        $where[] = [
            'acode',
            '=',
            get_backend_lang()
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
            $post = $this->getPostData('row/a', true);
            $sync = (array) ($post['sync'] ?? []);
            unset($post['sync']);
            // 构建数据
            $default = [
                'acode' => get_backend_lang(),
                'gid' => 0,
                'pic' => '',
                'link' => '',
                'title' => '',
                'subtitle' => '',
                'sorting' => 255,
                'create_user' => $this->auth->username,
                'update_user' => $this->auth->username
            ];
            $post = array_merge($default, $post);

            $slideModel = $this->model;
            if ($post['gid'] == 0) {
                $gid = $slideModel
                    ->where('acode', get_backend_lang())
                    ->order('gid', 'desc')
                    ->value('gid');
                $post['gid'] = ((int) $gid) + 1;
            }
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
                $this->syncSlides($post, get_backend_lang(), null, $sync);
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
            $sync = (array) ($post['sync'] ?? []);
            unset($post['sync']);
            $previous = $row->toArray();

            $result = false;
            $this->model->startTrans();
            try {
                $context = new \badou\EventContext($post, ['row' => $row]);
                $this->triggerObserver('BeforeEdit', $context, $this);
                if ($context->isIntercepted()) {
                    $this->error($context->getMessage());
                }
                $post = $context->getData();

                if ((int) ($post['gid'] ?? 0) <= 0) {
                    $post['gid'] = (int) $row->getAttr('gid');
                }
                if ((int) $post['gid'] <= 0) {
                    $post['gid'] = ((int) $this->model
                        ->where('acode', $row->getAttr('acode') ?: get_backend_lang())
                        ->max('gid')) + 1;
                }

                $result = $row->save($post);
                $this->syncSlides($row->toArray(), get_backend_lang(), $previous, $sync);
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

    /** 同步轮播图到选中的启用语言；编辑时更新已有项，缺失项自动补建。 */
    protected function syncSlides(array $source, string $sourceAcode, ?array $previous = null, array $targetAcodes = []): void
    {
        $targetAcodes = array_values(array_unique(array_filter(array_map('strval', $targetAcodes), function ($acode) use ($sourceAcode) {
            return $acode !== '' && $acode !== $sourceAcode;
        })));
        if (!$targetAcodes) {
            return;
        }

        $base = $previous ?: $source;
        $position = null;
        if ($previous) {
            $ids = $this->model
                ->where('acode', $sourceAcode)
                ->where('gid', $previous['gid'])
                ->order('sorting ASC,id ASC')
                ->column('id');
            $position = array_search((string) $previous['id'], array_map('strval', $ids), true);
            $position = $position === false ? 0 : $position;
        }

        $languages = (new CmsArea())
            ->where('status', 1)
            ->where('acode', 'in', $targetAcodes)
            ->order('is_default DESC,id ASC')
            ->column('acode');
        $matchFields = array_values(array_intersect(
            ['gid', 'type', 'pic', 'video', 'link', 'title', 'subtitle', 'sorting'],
            $this->slideTableFields(),
            array_keys($base)
        ));

        foreach (array_unique(array_filter(array_map('strval', $languages))) as $acode) {
            if ($acode === $sourceAcode) {
                continue;
            }

            $query = $this->model->where('acode', $acode);
            foreach ($matchFields as $field) {
                $query->where($field, $base[$field]);
            }
            $target = $query->find();

            if (!$target && $position !== null) {
                $rows = $this->model
                    ->where('acode', $acode)
                    ->where('gid', $base['gid'])
                    ->order('sorting ASC,id ASC')
                    ->select();
                $target = $rows[$position] ?? null;
            }

            $data = $this->slideSyncData($source, $acode);
            if ($target) {
                unset($data['create_user']);
                $target->save($data);
            } else {
                (new \app\admin\model\cms\Slide())->save($data);
            }
        }
    }

    /** 只同步轮播数据字段，避免覆盖目标语言的主键和创建时间。 */
    protected function slideSyncData(array $source, string $acode): array
    {
        $fields = array_values(array_intersect(
            ['gid', 'type', 'pic', 'video', 'link', 'title', 'subtitle', 'sorting', 'create_user', 'update_user'],
            $this->slideTableFields()
        ));
        $data = array_intersect_key($source, array_flip($fields));
        $data['acode'] = $acode;
        return $data;
    }

    /** 获取当前轮播表实际存在的字段，兼容 Inquiry 对 type、video 的扩展。 */
    protected function slideTableFields(): array
    {
        static $fields;

        if ($fields === null) {
            $fields = array_keys($this->model->getFields());
        }

        return $fields;
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

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

use Exception;
use Throwable;
use think\facade\Db;

/**
 * CMS文章内容
 */
class Content extends Base
{
    /**
     * Content模型对象
     * @var \app\admin\model\cms\Content
     * @phpstan-var \app\admin\model\cms\Content
     */
    protected object $model;
    /**
     * Contentext模型对象
     * @var \app\admin\model\cms\ContentExt
     * @phpstan-var \app\admin\model\cms\ContentExt
     */
    protected object $contentExtModel;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];
    protected string|array $quickSearchField = ['id','title'];
    protected string $weighField = 'sorting';

    /* 设置默认排序 */
    protected array|string $defaultSortField = [
        'istop' => 'desc',
        'isrecommend' => 'desc',
        'isheadline' => 'desc',
        'sorting' => 'asc',
        'date' => 'desc',
        'id' => 'desc'
    ];

    /**
     * 模型ID
     * @var string
     */
    protected int $mcode = 0;

    protected array $withJoinTable = ['contentsort'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Content();
        $this->contentExtModel = new \app\admin\model\cms\ContentExt();
        $this->mcode = $this->request->param('mcode') ?? 0;
    }

    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        $contentsortModel = new \app\admin\model\cms\ContentSort();

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        foreach ($where as &$whereitem) {
            if ($whereitem[0] == 'content.scode') {
                $whereitem[1] = 'in';
                $whereitem[2] = $contentsortModel->getChildrenIds($whereitem[2], true, true);
            }

            // 因为date是时间格式 所以要进行处理
            if ($whereitem[0] == 'content.date') {
                foreach ($whereitem[2] as $key => $value) {
                    $whereitem[2][$key] = date('Y-m-d H:i:s', $value);
                }
            }
        }
        unset($whereitem);
        if ($this->mcode) {
            $where[] = [
                'contentsort.mcode',
                '=',
                $this->mcode
            ];
        }
        $where[] = [
            'content.acode','=',get_backend_lang()
        ];

        /* 查询子栏目数据 */
        $res = $this->model
            ->field($this->indexField)
            ->alias($alias)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 添加
     */
    public function add(): void
    {
        if ($this->request->isPost()) {

            $data = $this->getPostData();
            $data['content'] ?? $data['content'] = '';
            $data['content'] = $this->request->param('content', '', 'clean_xss');

            // 构建数据
            $default = [
                'acode' => get_backend_lang(),
                'scode' => '',
                'subscode' => '',
                'title' => '',
                'titlecolor' => '',
                'subtitle' => '',
                'filename' => '',
                'author' => $this->auth->nickname,
                'source' => '',
                'outlink' => '',
                'date' => '',
                'ico' => '',
                'pics' => '',
                'picstitle' => '',
                'content' => '',
                'tags' => '',
                'enclosure' => '',
                'keywords' => '',
                'description' => '',
                'sorting' => 255,
                'status' => 1,
                'istop' => 0,
                'isrecommend' => 0,
                'isheadline' => 0,
                'gid' => '',
                'gtype' => '',
                'gnote' => '',
                'visits' => 0,
                'likes' => 0,
                'oppose' => 0,
                'create_user' => $this->auth->username,
                'update_user' => $this->auth->username
            ];

            $data = array_merge($default, $data);

            $result = false;
            Db::startTrans();
            try {
                // 模型验证
                $this->modelValidateFunction($data);

                if ($data['filename'] && ! preg_match('/^[a-zA-Z0-9\-_\/]+$/', $data['filename'])) {
                    throw new Exception(__('URL name only allows letters, numbers, lines, underscores'));
                }

                // 自动提起前一百个字符为描述
                if (! $data['description'] && isset($data['content'])) {
                    $data['description'] = escape_string(clear_html_blank(substr_both(strip_tags($data['content']), 0, 150)));
                }

                // 无缩略图时，自动提取文章第一张图为缩略图
                if (! $data['ico'] && preg_match('/<img\s+.*?src=\s?[\'|\"](.*?(\.gif|\.jpg|\.png|\.jpeg))[\'|\"].*?[\/]?>/i', decode_string($data['content']), $srcs) && isset($srcs[1])) {
                    $data['ico'] = $srcs[1];
                }

                // 检查自定义URL名称
                if ($data['filename']) {
                    while ($this->model->checkFilename($data['filename'])) {
                        $data['filename'] = $data['filename'] . '-' . mt_rand(1, 20);
                    }
                }

                $result = $this->model->save($data);

                /* 添加扩展数据 */
                $extdata = $this->contentExtModel->getExtData($data, $this->mcode);
                $extdata['contentid'] = $this->model->id;

                $this->contentExtModel->save($extdata);

                Db::commit();
            } catch (Throwable $e) {
                Db::rollback();
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
     * 修改
     * @return void
     */
    public function edit(): void
    {
        $pk  = $this->model->getPk();
        $id  = $this->request->param($pk);
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }
        /* 获取扩展数据 */
        $extRow = $this->contentExtModel->where('contentid', $row['id'])->find();
        if ($extRow) {
            /* 合并数据 */
            $extRowArr = $extRow->toArray();
            $extRowArr = $this->contentExtModel->formatValue($this->mcode, $extRowArr);
            $row->appendData($extRowArr);
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->getPostData();
            $data['filename'] ?? $data['filename'] = $row['filename'];
            $data['description'] ?? $data['description'] = $row['description'];
            $data['ico'] ?? $data['ico'] = $row['ico'];
            $data['scode'] ?? $data['scode'] = $row['scode'];
            $data['title'] ?? $data['title'] = $row['title'];
            $data['content'] = $this->request->param('content', $row['content'], 'clean_xss');
            $result = false;
            $this->model->startTrans();
            try {
                $data[$pk] = $row[$pk];
                $this->modelValidateFunction($data);

                if ($data['filename'] && ! preg_match('/^[a-zA-Z0-9\-_\/]+$/', $data['filename'])) {
                    throw new Exception(__('URL name only allows letters, numbers, lines, underscores'));
                }

                // 自动提起前一百个字符为描述
                if (! $data['description'] && isset($data['content'])) {
                    $data['description'] = escape_string(clear_html_blank(substr_both(strip_tags($data['content']), 0, 150)));
                }

                // 无缩略图时，自动提取文章第一张图为缩略图
                if (! $data['ico'] && preg_match('/<img\s+.*?src=\s?[\'|\"](.*?(\.gif|\.jpg|\.png|\.jpeg))[\'|\"].*?[\/]?>/i', decode_string($data['content']), $srcs) && isset($srcs[1])) {
                    $data['ico'] = $srcs[1];
                }

                // 检查自定义URL名称
                if ($data['filename']) {
                    while ($this->model->checkFilename($data['filename'])) {
                        $data['filename'] = $data['filename'] . '-' . mt_rand(1, 20);
                    }
                }

                $result = $row->save($data);
                /* 添加扩展数据 */
                $extdata = $this->contentExtModel->getExtData($data, $this->mcode);
                $extdata['contentid'] = $row['id'];

                if ($extRow) {
                    $extRow->save($extdata);
                } else {
                    $this->contentExtModel->save($extdata);
                }

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

        $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 复制数据
     * @return void
     */
    public function copy(): void
    {
        $ids = $this->request->param('ids/a');
        $scode = $this->request->param('scode');
        if (count($ids) <= 0) {
            $this->error(__('Please select the data'));
        }
        if (! $scode) {
            $this->error(__('Please Select a Category'));
        }
        $this->model->copyContent($ids, $scode);
        $this->success(__('Copy successful'));
    }

    /**
     * 移动数据
     * @return void
     */
    public function move(): void
    {
        $ids = $this->request->param('ids/a');
        $scode = $this->request->param('scode');
        if (count($ids) <= 0) {
            $this->error(__('Please select the data'));
        }
        if (! $scode) {
            $this->error(__('Please Select a Category'));
        }
        $this->model->moveContent($ids, $scode);
        $this->success(__('Move successful'));
    }
}

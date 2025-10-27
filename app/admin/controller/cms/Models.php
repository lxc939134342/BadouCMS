<?php

namespace app\admin\controller\cms;

use Throwable;
use think\Exception;
use think\facade\Db;
use badou\Filesystem;

class Models extends Base
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Models();
        $typeList = $this->model->getTypeList();
        $this->assign("type_list", $typeList);
        $this->assignconfig("type_list", $this->model->getTypeList());
        $this->assign('tpls', $this->getTpls());
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->getPostData('row/a');
            // 构建数据
            $default = array(
                'mcode' => 0,
                'name' => '',
                'type' => '',
                'urlname' => '',
                'listtpl' => '',
                'contenttpl' => '',
                'status' => 1,
                'issystem' => 0,
                'create_user' => $this->auth->username,
                'update_user' => $this->auth->username
            );

            $data = array_merge($default, $data);
            $result = false;
            $this->model->startTrans();
            try {
                $data['mcode'] = get_auto_code($this->model->getLastCode());
                // 模型验证
                $this->modelValidateFunction($data);
                $result = $this->model->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Add successful'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        return $this->fetch();
    }

    public function del()
    {
        $ids = $this->request->param('ids');

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
                if ($v['issystem'] == 1) {
                    throw new Exception(__('System model cannot be deleted'));
                }
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
     * 获取模版文件列表
     */
    protected function getTpls()
    {
        $default_code = Db::name('cms_area')->where('is_default', 1)->value('acode');
        $template = Db::name('cms_site')->where('acode', $default_code)->value('theme');
        $template=$template?:'default';
        $path = root_path().'template'.DIRECTORY_SEPARATOR.'cms'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR;;
        $list = [];
        if (is_dir($path)) {
            $files = Filesystem::getDirFiles($path, ['html']);
            $list = [];
            foreach ($files as $key => $value) {
                $list[] = ['id' => $key,'name' => $value];
            }
        }

        return $list;
    }
}

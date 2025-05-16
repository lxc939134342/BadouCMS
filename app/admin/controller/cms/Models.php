<?php

namespace app\admin\controller\cms;

class Models extends Base
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Models();
        $this->assignconfig("type_list", $this->model->getTypeList());
    }

}

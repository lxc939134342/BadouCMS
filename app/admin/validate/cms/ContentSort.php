<?php

namespace app\admin\validate\cms;

use think\Validate;
use think\facade\Db;

class ContentSort extends Validate
{
    protected $failException = true;

    protected $rule = [
        'name' => 'require',
        'mcode' => 'require',
        'aucode' => 'regex:/^[a-z0-9_]+$/|checkAucodeUnique'
    ];

    protected $message = [

    ];

    public function __construct()
    {
        $this->field = [
           'name' => __('Name'),
           'mcode' => __('Mcode'),
           'aucode' => __('Aucode'),
        ];

        $this->message = [
            'aucode.regex' => __('The generic code only supports lowercase letters,_, and numbers'),
            'aucode.checkAucodeUnique' => __('The generic code already exists'),
        ];
        parent::__construct();
    }

    /**
     * 验证aucode唯一性
     * @param string $value 验证字段的值
     * @param mixed $rule 验证规则
     * @param array $data 全部数据
     * @return bool
     */
    protected function checkAucodeUnique($value, $rule, $data)
    {
        // 获取当前语言编码
        $acode = get_backend_lang();

        // 查询是否存在相同的aucode
        $where = [
            ['aucode', '=', $value],
            ['acode', '=', $acode]
        ];

        // 如果是编辑操作，需要排除当前记录
        if (isset($data['id']) && $data['id']) {
            $where[] = ['id', '<>', $data['id']];
        }

        $result = Db::name('cms_content_sort')->where($where)->find();

        // 如果查询到结果，说明已存在
        if ($result) {
            return false;
        }

        return true;
    }
}

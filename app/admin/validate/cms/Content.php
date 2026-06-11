<?php

namespace app\admin\validate\cms;

use think\Validate;
use think\facade\Db;

class Content extends Validate
{
    protected $failException = true;

    protected $rule = [
        'filename' => 'regex:/^[a-zA-Z0-9\-_\/]+$/',
        'aucode' => 'checkAucodeFormat|checkAucodeUnique',
    ];

    protected $message = [

    ];

    public function __construct()
    {
        $this->field = [
           'filename' => __('URL name'),
           'aucode' => __('Aucode'),
        ];

        $this->message = [
            'filename.regex' => __('URL name only allows letters, numbers, lines, underscores'),
            'aucode.checkAucodeFormat' => __('The generic code only supports letters,_, and numbers'),
            'aucode.checkAucodeUnique' => __('The generic code already exists'),
        ];
        parent::__construct();
    }

    /**
     * 验证 aucode 格式
     * @param string $value 验证字段的值
     * @param mixed $rule 验证规则
     * @param array $data 全部数据
     * @return bool
     */
    protected function checkAucodeFormat($value, $rule, $data): bool
    {
        if ($value === '' || is_null($value)) {
            return true;
        }

        return (bool) preg_match('/^[a-zA-Z0-9_]+$/', $value);
    }

    /**
     * 验证当前语言下 aucode 唯一性
     * @param string $value 验证字段的值
     * @param mixed $rule 验证规则
     * @param array $data 全部数据
     * @return bool
     */
    protected function checkAucodeUnique($value, $rule, $data)
    {
        if ($value === '' || is_null($value)) {
            return true;
        }

        $where = [
            ['aucode', '=', $value],
            ['acode', '=', $data['acode'] ?? get_backend_lang()],
        ];

        if (!empty($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }

        return !Db::name('cms_content')->where($where)->find();
    }
}

<?php

namespace app\admin\validate\cms;

use think\Validate;

class Content extends Validate
{
    protected $failException = true;

    protected $rule = [
        'filename' => 'regex:/^[a-zA-Z0-9\-_\/]+$/'
    ];

    protected $message = [

    ];

    public function __construct()
    {
        $this->field = [
           'filename' => __('URL name'),
        ];

        $this->message = [
            'filename.regex' => __('URL name only allows letters, numbers, lines, underscores'),
        ];
        parent::__construct();
    }
}

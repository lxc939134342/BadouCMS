<?php

namespace app\admin\controller;

use app\common\controller\Backend;

class Addon extends Backend
{
    public function initialize(): void
    {
        parent::initialize();
    }

    public function index(): void
    {
        $this->success('', []);
    }
}

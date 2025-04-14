<?php

use think\facade\Route;

Route::group('list', function () {
    Route::rule(':scode', 'index')
        ->option(['rule',['page', 'num', 'order']]);
})->prefix('cms.lists/');

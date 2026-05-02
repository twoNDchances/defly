<?php

use Illuminate\Support\Facades\Route;

Route::name('defly_manager.api.')
    ->prefix(config('customization.backend.urls.api_prefix'))
    ->group(function () {
        Route::get('');
    });

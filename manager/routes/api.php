<?php

use App\Http\Middleware\AuthenticateApiKey;
use Illuminate\Support\Facades\Route;

Route::name('defly_manager.api.')
    ->prefix(config('customization.backend.urls.api_prefix'))
    ->middleware(AuthenticateApiKey::class)
    ->group(function () {
        Route::get('');
    });

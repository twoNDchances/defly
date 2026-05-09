<?php

use App\Http\Controllers\MeController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiLocale;
use App\Http\Middleware\ApiToken;
use Illuminate\Support\Facades\Route;

Route::name('defly_manager.api.')
    ->prefix(config('customization.backend.urls.api_prefix'))
    ->middleware([
        ApiLocale::class,
        ApiToken::class,
    ])
    ->group(function () {
        Route::get('me/payload', [MeController::class, 'payload'])->name('me.payload');
        Route::get('me', [MeController::class, 'show'])->name('me.show');
        Route::match(['put', 'patch'], 'me', [MeController::class, 'update'])->name('me.update');

        Route::get('users/payload', [UserController::class, 'payload'])->name('users.payload');
        Route::apiResource('users', UserController::class);
    });

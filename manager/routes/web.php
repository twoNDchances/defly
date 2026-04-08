<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::name('defly_manager.')
    ->prefix(config('customization.backend.urls.gui_prefix'))
    ->group(function () {
        Route::get('verify/{email}/{token}', [UserController::class, 'verify'])->name('verification_mail');
    });

<?php

use App\Http\Controllers\ActionController;
use App\Http\Controllers\DecisionController;
use App\Http\Controllers\DefenderController;
use App\Http\Controllers\EngineController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\PatternController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PrincipleController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\TargetController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WordlistController;
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

        Route::get('actions/payload', [ActionController::class, 'payload'])->name('actions.payload');
        Route::apiResource('actions', ActionController::class);

        Route::get('decisions/payload', [DecisionController::class, 'payload'])->name('decisions.payload');
        Route::apiResource('decisions', DecisionController::class);

        Route::get('defenders/payload', [DefenderController::class, 'payload'])->name('defenders.payload');
        Route::apiResource('defenders', DefenderController::class);

        Route::get('engines/payload', [EngineController::class, 'payload'])->name('engines.payload');
        Route::apiResource('engines', EngineController::class);

        Route::get('groups/payload', [GroupController::class, 'payload'])->name('groups.payload');
        Route::apiResource('groups', GroupController::class);

        Route::get('labels/payload', [LabelController::class, 'payload'])->name('labels.payload');
        Route::apiResource('labels', LabelController::class);

        Route::apiResource('patterns', PatternController::class)->only(['index', 'show']);

        Route::get('permissions/payload', [PermissionController::class, 'payload'])->name('permissions.payload');
        Route::apiResource('permissions', PermissionController::class);

        Route::get('principles/payload', [PrincipleController::class, 'payload'])->name('principles.payload');
        Route::apiResource('principles', PrincipleController::class);

        Route::get('rules/payload', [RuleController::class, 'payload'])->name('rules.payload');
        Route::apiResource('rules', RuleController::class);

        Route::get('targets/payload', [TargetController::class, 'payload'])->name('targets.payload');
        Route::apiResource('targets', TargetController::class);

        Route::apiResource('timelines', TimelineController::class)->only(['index', 'show', 'destroy']);

        Route::get('wordlists/payload', [WordlistController::class, 'payload'])->name('wordlists.payload');
        Route::apiResource('wordlists', WordlistController::class);
    });

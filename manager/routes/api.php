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
use App\Http\Controllers\ReportController;
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
        Route::get('users/{user}/permissions', [UserController::class, 'permissions'])->name('users.permissions.index');
        Route::post('users/{user}/permissions', [UserController::class, 'attachPermissions'])->name('users.permissions.attach');
        Route::delete('users/{user}/permissions', [UserController::class, 'detachPermissions'])->name('users.permissions.detach');
        Route::get('users/{user}/groups', [UserController::class, 'groups'])->name('users.groups.index');
        Route::post('users/{user}/groups', [UserController::class, 'attachGroups'])->name('users.groups.attach');
        Route::delete('users/{user}/groups', [UserController::class, 'detachGroups'])->name('users.groups.detach');
        Route::get('users/{user}/labels', [UserController::class, 'labels'])->name('users.labels.index');
        Route::post('users/{user}/labels', [UserController::class, 'attachLabels'])->name('users.labels.attach');
        Route::delete('users/{user}/labels', [UserController::class, 'detachLabels'])->name('users.labels.detach');
        Route::apiResource('users', UserController::class);

        Route::get('actions/payload', [ActionController::class, 'payload'])->name('actions.payload');
        Route::get('actions/{action}/labels', [ActionController::class, 'labels'])->name('actions.labels.index');
        Route::post('actions/{action}/labels', [ActionController::class, 'attachLabels'])->name('actions.labels.attach');
        Route::delete('actions/{action}/labels', [ActionController::class, 'detachLabels'])->name('actions.labels.detach');
        Route::apiResource('actions', ActionController::class);

        Route::get('decisions/payload', [DecisionController::class, 'payload'])->name('decisions.payload');
        Route::get('decisions/{decision}/labels', [DecisionController::class, 'labels'])->name('decisions.labels.index');
        Route::post('decisions/{decision}/labels', [DecisionController::class, 'attachLabels'])->name('decisions.labels.attach');
        Route::delete('decisions/{decision}/labels', [DecisionController::class, 'detachLabels'])->name('decisions.labels.detach');
        Route::apiResource('decisions', DecisionController::class);

        Route::get('defenders/payload', [DefenderController::class, 'payload'])->name('defenders.payload');
        Route::post('defenders/{defender}/deploy', [DefenderController::class, 'deploy'])->name('defenders.deploy');
        Route::post('defenders/{defender}/cancel', [DefenderController::class, 'cancel'])->name('defenders.cancel');
        Route::post('defenders/{defender}/follow', [DefenderController::class, 'follow'])->name('defenders.follow');
        Route::get('defenders/{defender}/principles', [DefenderController::class, 'principles'])->name('defenders.principles.index');
        Route::post('defenders/{defender}/principles', [DefenderController::class, 'attachPrinciples'])->name('defenders.principles.attach');
        Route::delete('defenders/{defender}/principles', [DefenderController::class, 'detachPrinciples'])->name('defenders.principles.detach');
        Route::post('defenders/{defender}/principles/{principle}/apply', [DefenderController::class, 'applyPrinciple'])->name('defenders.principles.apply');
        Route::post('defenders/{defender}/principles/{principle}/revoke', [DefenderController::class, 'revokePrinciple'])->name('defenders.principles.revoke');
        Route::get('defenders/{defender}/decisions', [DefenderController::class, 'decisions'])->name('defenders.decisions.index');
        Route::post('defenders/{defender}/decisions', [DefenderController::class, 'attachDecisions'])->name('defenders.decisions.attach');
        Route::delete('defenders/{defender}/decisions', [DefenderController::class, 'detachDecisions'])->name('defenders.decisions.detach');
        Route::post('defenders/{defender}/decisions/{decision}/implement', [DefenderController::class, 'implementDecision'])->name('defenders.decisions.implement');
        Route::post('defenders/{defender}/decisions/{decision}/suspend', [DefenderController::class, 'suspendDecision'])->name('defenders.decisions.suspend');
        Route::get('defenders/{defender}/labels', [DefenderController::class, 'labels'])->name('defenders.labels.index');
        Route::post('defenders/{defender}/labels', [DefenderController::class, 'attachLabels'])->name('defenders.labels.attach');
        Route::delete('defenders/{defender}/labels', [DefenderController::class, 'detachLabels'])->name('defenders.labels.detach');
        Route::get('defenders/{defender}/reports', [ReportController::class, 'index'])->name('defenders.reports.index');
        Route::get('defenders/{defender}/reports/{report}', [ReportController::class, 'show'])->name('defenders.reports.show');
        Route::delete('defenders/{defender}/reports/{report}', [ReportController::class, 'destroy'])->name('defenders.reports.destroy');
        Route::apiResource('defenders', DefenderController::class);

        Route::get('engines/payload', [EngineController::class, 'payload'])->name('engines.payload');
        Route::get('engines/{engine}/labels', [EngineController::class, 'labels'])->name('engines.labels.index');
        Route::post('engines/{engine}/labels', [EngineController::class, 'attachLabels'])->name('engines.labels.attach');
        Route::delete('engines/{engine}/labels', [EngineController::class, 'detachLabels'])->name('engines.labels.detach');
        Route::apiResource('engines', EngineController::class);

        Route::get('groups/payload', [GroupController::class, 'payload'])->name('groups.payload');
        Route::get('groups/{group}/users', [GroupController::class, 'users'])->name('groups.users.index');
        Route::post('groups/{group}/users', [GroupController::class, 'attachUsers'])->name('groups.users.attach');
        Route::delete('groups/{group}/users', [GroupController::class, 'detachUsers'])->name('groups.users.detach');
        Route::get('groups/{group}/permissions', [GroupController::class, 'permissions'])->name('groups.permissions.index');
        Route::post('groups/{group}/permissions', [GroupController::class, 'attachPermissions'])->name('groups.permissions.attach');
        Route::delete('groups/{group}/permissions', [GroupController::class, 'detachPermissions'])->name('groups.permissions.detach');
        Route::get('groups/{group}/labels', [GroupController::class, 'labels'])->name('groups.labels.index');
        Route::post('groups/{group}/labels', [GroupController::class, 'attachLabels'])->name('groups.labels.attach');
        Route::delete('groups/{group}/labels', [GroupController::class, 'detachLabels'])->name('groups.labels.detach');
        Route::apiResource('groups', GroupController::class);

        Route::get('labels/payload', [LabelController::class, 'payload'])->name('labels.payload');
        Route::get('labels/{label}/users', [LabelController::class, 'users'])->name('labels.users.index');
        Route::post('labels/{label}/users', [LabelController::class, 'attachUsers'])->name('labels.users.attach');
        Route::delete('labels/{label}/users', [LabelController::class, 'detachUsers'])->name('labels.users.detach');
        Route::get('labels/{label}/permissions', [LabelController::class, 'permissions'])->name('labels.permissions.index');
        Route::post('labels/{label}/permissions', [LabelController::class, 'attachPermissions'])->name('labels.permissions.attach');
        Route::delete('labels/{label}/permissions', [LabelController::class, 'detachPermissions'])->name('labels.permissions.detach');
        Route::get('labels/{label}/groups', [LabelController::class, 'groups'])->name('labels.groups.index');
        Route::post('labels/{label}/groups', [LabelController::class, 'attachGroups'])->name('labels.groups.attach');
        Route::delete('labels/{label}/groups', [LabelController::class, 'detachGroups'])->name('labels.groups.detach');
        Route::get('labels/{label}/wordlists', [LabelController::class, 'wordlists'])->name('labels.wordlists.index');
        Route::post('labels/{label}/wordlists', [LabelController::class, 'attachWordlists'])->name('labels.wordlists.attach');
        Route::delete('labels/{label}/wordlists', [LabelController::class, 'detachWordlists'])->name('labels.wordlists.detach');
        Route::get('labels/{label}/engines', [LabelController::class, 'engines'])->name('labels.engines.index');
        Route::post('labels/{label}/engines', [LabelController::class, 'attachEngines'])->name('labels.engines.attach');
        Route::delete('labels/{label}/engines', [LabelController::class, 'detachEngines'])->name('labels.engines.detach');
        Route::get('labels/{label}/targets', [LabelController::class, 'targets'])->name('labels.targets.index');
        Route::post('labels/{label}/targets', [LabelController::class, 'attachTargets'])->name('labels.targets.attach');
        Route::delete('labels/{label}/targets', [LabelController::class, 'detachTargets'])->name('labels.targets.detach');
        Route::get('labels/{label}/actions', [LabelController::class, 'actions'])->name('labels.actions.index');
        Route::post('labels/{label}/actions', [LabelController::class, 'attachActions'])->name('labels.actions.attach');
        Route::delete('labels/{label}/actions', [LabelController::class, 'detachActions'])->name('labels.actions.detach');
        Route::get('labels/{label}/rules', [LabelController::class, 'rules'])->name('labels.rules.index');
        Route::post('labels/{label}/rules', [LabelController::class, 'attachRules'])->name('labels.rules.attach');
        Route::delete('labels/{label}/rules', [LabelController::class, 'detachRules'])->name('labels.rules.detach');
        Route::get('labels/{label}/principles', [LabelController::class, 'principles'])->name('labels.principles.index');
        Route::post('labels/{label}/principles', [LabelController::class, 'attachPrinciples'])->name('labels.principles.attach');
        Route::delete('labels/{label}/principles', [LabelController::class, 'detachPrinciples'])->name('labels.principles.detach');
        Route::get('labels/{label}/decisions', [LabelController::class, 'decisions'])->name('labels.decisions.index');
        Route::post('labels/{label}/decisions', [LabelController::class, 'attachDecisions'])->name('labels.decisions.attach');
        Route::delete('labels/{label}/decisions', [LabelController::class, 'detachDecisions'])->name('labels.decisions.detach');
        Route::get('labels/{label}/defenders', [LabelController::class, 'defenders'])->name('labels.defenders.index');
        Route::post('labels/{label}/defenders', [LabelController::class, 'attachDefenders'])->name('labels.defenders.attach');
        Route::delete('labels/{label}/defenders', [LabelController::class, 'detachDefenders'])->name('labels.defenders.detach');
        Route::apiResource('labels', LabelController::class);

        Route::apiResource('patterns', PatternController::class)->only(['index', 'show']);

        Route::get('permissions/payload', [PermissionController::class, 'payload'])->name('permissions.payload');
        Route::get('permissions/{permission}/users', [PermissionController::class, 'users'])->name('permissions.users.index');
        Route::post('permissions/{permission}/users', [PermissionController::class, 'attachUsers'])->name('permissions.users.attach');
        Route::delete('permissions/{permission}/users', [PermissionController::class, 'detachUsers'])->name('permissions.users.detach');
        Route::get('permissions/{permission}/groups', [PermissionController::class, 'groups'])->name('permissions.groups.index');
        Route::post('permissions/{permission}/groups', [PermissionController::class, 'attachGroups'])->name('permissions.groups.attach');
        Route::delete('permissions/{permission}/groups', [PermissionController::class, 'detachGroups'])->name('permissions.groups.detach');
        Route::get('permissions/{permission}/labels', [PermissionController::class, 'labels'])->name('permissions.labels.index');
        Route::post('permissions/{permission}/labels', [PermissionController::class, 'attachLabels'])->name('permissions.labels.attach');
        Route::delete('permissions/{permission}/labels', [PermissionController::class, 'detachLabels'])->name('permissions.labels.detach');
        Route::apiResource('permissions', PermissionController::class);

        Route::get('principles/payload', [PrincipleController::class, 'payload'])->name('principles.payload');
        Route::get('principles/{principle}/rules', [PrincipleController::class, 'rules'])->name('principles.rules.index');
        Route::post('principles/{principle}/rules', [PrincipleController::class, 'attachRules'])->name('principles.rules.attach');
        Route::delete('principles/{principle}/rules', [PrincipleController::class, 'detachRules'])->name('principles.rules.detach');
        Route::get('principles/{principle}/labels', [PrincipleController::class, 'labels'])->name('principles.labels.index');
        Route::post('principles/{principle}/labels', [PrincipleController::class, 'attachLabels'])->name('principles.labels.attach');
        Route::delete('principles/{principle}/labels', [PrincipleController::class, 'detachLabels'])->name('principles.labels.detach');
        Route::post('principles/{principle}/validate', [PrincipleController::class, 'validate'])->name('principles.validate');
        Route::apiResource('principles', PrincipleController::class);

        Route::get('rules/payload', [RuleController::class, 'payload'])->name('rules.payload');
        Route::get('rules/{rule}/actions', [RuleController::class, 'actions'])->name('rules.actions.index');
        Route::post('rules/{rule}/actions', [RuleController::class, 'attachActions'])->name('rules.actions.attach');
        Route::delete('rules/{rule}/actions', [RuleController::class, 'detachActions'])->name('rules.actions.detach');
        Route::get('rules/{rule}/labels', [RuleController::class, 'labels'])->name('rules.labels.index');
        Route::post('rules/{rule}/labels', [RuleController::class, 'attachLabels'])->name('rules.labels.attach');
        Route::delete('rules/{rule}/labels', [RuleController::class, 'detachLabels'])->name('rules.labels.detach');
        Route::apiResource('rules', RuleController::class);

        Route::get('targets/payload', [TargetController::class, 'payload'])->name('targets.payload');
        Route::get('targets/{target}/engines', [TargetController::class, 'engines'])->name('targets.engines.index');
        Route::post('targets/{target}/engines', [TargetController::class, 'attachEngines'])->name('targets.engines.attach');
        Route::delete('targets/{target}/engines', [TargetController::class, 'detachEngines'])->name('targets.engines.detach');
        Route::get('targets/{target}/labels', [TargetController::class, 'labels'])->name('targets.labels.index');
        Route::post('targets/{target}/labels', [TargetController::class, 'attachLabels'])->name('targets.labels.attach');
        Route::delete('targets/{target}/labels', [TargetController::class, 'detachLabels'])->name('targets.labels.detach');
        Route::apiResource('targets', TargetController::class);

        Route::apiResource('timelines', TimelineController::class)->only(['index', 'show', 'destroy']);

        Route::get('wordlists/payload', [WordlistController::class, 'payload'])->name('wordlists.payload');
        Route::get('wordlists/{wordlist}/labels', [WordlistController::class, 'labels'])->name('wordlists.labels.index');
        Route::post('wordlists/{wordlist}/labels', [WordlistController::class, 'attachLabels'])->name('wordlists.labels.attach');
        Route::delete('wordlists/{wordlist}/labels', [WordlistController::class, 'detachLabels'])->name('wordlists.labels.detach');
        Route::apiResource('wordlists', WordlistController::class);
    });

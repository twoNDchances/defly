<?php

namespace App\Services;

use App\Models\Key;
use App\Models\User;

class Security
{
    public static $models = [
        'User',
        'Group',
        'Permission',
    ];

    public static $actions = [
        'all' => 'Full',
        'viewAny' => 'List',
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Update',
        'deleteAny' => 'Multi-delete',
        'delete' => 'Delete',
        'clone' => 'Clone',
        'validate' => 'Validate',
        'validateAny' => 'Multi-validate',
        'deploy' => 'Deploy',
        'deployAny' => 'Multi-deploy',
        'cancel' => 'Cancel',
        'cancelAny' => 'Multi-cancel',
        'follow' => 'Follow',
        'refresh' => 'Refresh',
        'apply' => 'Apply',
        'revoke' => 'Revoke',
        'implement' => 'Implement',
        'suspend' => 'Suspend',
    ];

    public static $excludeActionsByModel = [
        'Pattern' => [
            'create',
            'update',
            'deleteAny',
            'delete',
        ],
    ];

    public static function generatePermissionList($groupByModel = false)
    {
        $policiesPath = app_path('Policies');
        $files = glob("$policiesPath/*.php");

        $permissions = [];
        $groupedActions = [];

        foreach ($files as $file) {
            $className = 'App\\Policies\\'.basename($file, '.php');

            if (! class_exists($className)) {
                require_once $file;
            }

            if (! class_exists($className)) {
                continue;
            }

            $model = preg_replace('/Policy$/', '', class_basename($className));

            $methods = get_class_methods($className);

            foreach ($methods as $method) {
                if (! isset(self::$actions[$method])) {
                    continue;
                }

                if (isset(self::$excludeActionsByModel[$model]) && in_array($method, self::$excludeActionsByModel[$model])) {
                    continue;
                }

                if ($groupByModel) {
                    $groupedActions[$model][$method] = self::$actions[$method];

                    continue;
                }

                $permissions[] = [
                    'name' => "$model:".self::$actions[$method],
                    'applied_for' => $model,
                    'action' => $method,
                ];
            }
        }

        return $groupByModel ? $groupedActions : $permissions;
    }

    public static function can($model, $action, ?User $user = null)
    {
        $user = $user ?: Identification::getCurrent();
        if (! $user || ! $user->is_verified || ! $user->is_activated) {
            return false;
        }

        $subject = self::getPermissionSubject($user);

        if ($subject instanceof User && $user->is_root) {
            return true;
        }

        if (self::checkPermission($subject, $model, 'all')) {
            return true;
        }

        return self::checkPermission($subject, $model, $action);
    }

    public static function checkPermission(User|Key $subject, $model, $action)
    {
        $appliedFor = basename($model);
        $hasDirectPermission = $subject->permissions()
            ->where('action', $action)
            ->where('applied_for', $appliedFor)
            ->exists();

        if ($hasDirectPermission) {
            return true;
        }

        return $subject->groups()
            ->whereHas('permissions', function ($query) use ($action, $appliedFor) {
                $query->where('action', $action)
                    ->where('applied_for', $appliedFor);
            })
            ->exists();
    }

    private static function getPermissionSubject(User $user): User|Key
    {
        $key = request()?->attributes->get('authenticated_key');

        if ($key instanceof Key && ! $key->is_reused) {
            return $key;
        }

        return $user;
    }
}

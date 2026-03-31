<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Security
{
    public static $models = [
        'User',
        'Policy',
        'Permission',
    ];

    public static $actions = [
        'all' => 'Full',
        'accessOther' => 'Access other',
        'viewAny' => 'List',
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Update',
        'deleteAny' => 'Multi-delete',
        'delete' => 'Delete',
    ];

    public static function generatePermissionList()
    {
        $policiesPath = app_path('Policies');
        $files = glob("$policiesPath/*.php");

        $permissions = [];

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

                $permissions[] = [
                    'name' => "$model:".self::$actions[$method],
                    'applied_for' => $model,
                    'action' => $method,
                ];
            }
        }

        return $permissions;
    }

    public static function can($model, $action, ?User $user = null)
    {
        $user = $user ?: Identification::getCurrent();
        if (! $user || ! $user->is_verified || ! $user->is_activated) {
            return false;
        }
        if ($user->is_root || self::checkPermission($user, $model, 'all')) {
            return true;
        }

        return self::checkPermission($user, $model, $action);
    }

    public static function viewAnyOther($resource)
    {
        $query = $resource::query();
        if (! self::can($resource, 'accessOther')) {
            return $query->where('created_by', Identification::getCurrent());
        }

        return $query;
    }

    public static function checkPermission(User $user, $model, $action)
    {
        $appliedFor = basename($model);
        $hasDirectPermission = $user->permissions()
            ->where('action', $action)
            ->where('applied_for', $appliedFor)
            ->exists();

        if ($hasDirectPermission) {
            return true;
        }

        return $user->policies()
            ->whereHas('permissions', function ($query) use ($action, $appliedFor) {
                $query->where('action', $action)
                    ->where('applied_for', $appliedFor);
            })
            ->exists();
    }
}

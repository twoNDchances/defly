<?php

namespace App\Traits\Filament\Specifics\Permission;

use App\Traits\Filament\Generals\Components\Column;

trait PermissionColumn
{
    use Column, PermissionButton, PermissionData;

    public static function name()
    {
        return self::textColumn('name', __('tables.columns.permission.name'));
    }

    public static function appliedFor()
    {
        return self::textColumn('applied_for', __('tables.columns.permission.applied_for'));
    }

    public static function action()
    {
        return self::textColumn('action', __('tables.columns.permission.action'))
            ->getStateUsing(fn ($record) => self::permissionList()[$record->applied_for][$record->action]);
    }

    public static function users()
    {
        return self::relationshipColumn('users.email', __('tables.columns.permission.users'));
    }

    public static function policies()
    {
        return self::relationshipColumn('policies.name', __('tables.columns.permission.policies'));
    }
}

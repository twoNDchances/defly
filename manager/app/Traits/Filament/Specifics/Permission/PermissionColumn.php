<?php

namespace App\Traits\Filament\Specifics\Permission;

use App\Traits\Filament\Generals\Components\Column;

trait PermissionColumn
{
    use Column, PermissionButton, PermissionData;

    public static function getName()
    {
        return self::textColumn('name', __('models.permission.fields.name'));
    }

    public static function getAppliedFor()
    {
        return self::textColumn('applied_for', __('models.permission.fields.applied_for'));
    }

    public static function getAction()
    {
        return self::textColumn('action', __('models.permission.fields.action'))
            ->getStateUsing(fn ($record) => self::permissionList()[$record->applied_for][$record->action]);
    }

    public static function getUsers()
    {
        return self::relationshipColumn('users.email', __('tables.permission.users'));
    }

    public static function getPolicies()
    {
        return self::relationshipColumn('policies.name', __('tables.permission.policies'));
    }
}

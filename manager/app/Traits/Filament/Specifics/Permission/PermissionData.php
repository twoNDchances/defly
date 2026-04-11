<?php

namespace App\Traits\Filament\Specifics\Permission;

use App\Services\Security;

trait PermissionData
{
    public static function permissionList(): array
    {
        return Security::generatePermissionList(true);
    }

    public static function permissionModelOptions(): array
    {
        $models = array_keys(self::permissionList());

        return $models === [] ? [] : array_combine($models, $models);
    }
}

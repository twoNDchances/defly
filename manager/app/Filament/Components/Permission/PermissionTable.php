<?php

namespace App\Filament\Components\Permission;

use App\Traits\Filament\Specifics\Permission\PermissionColumn;

class PermissionTable
{
    use PermissionColumn;

    public static function build()
    {
        return [
            self::getName(),
            self::getAppliedFor(),
            self::getAction(),
            self::getUsers(),
            self::getGroups(),
            self::getLabels(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

<?php

namespace App\Filament\Components\Permission;

use App\Traits\Filament\Specifics\Permission\PermissionColumn;

class PermissionTable
{
    use PermissionColumn;

    public static function build()
    {
        return [
            self::name(),
            self::appliedFor(),
            self::action(),
            self::users(),
            self::policies(),
            self::labels(),
            self::createdBy(),
            self::createdAt(),
            self::updatedAt(),
        ];
    }
}

<?php

namespace App\Filament\Components\Group;

use App\Traits\Filament\Specifics\Group\GroupColumn;

class GroupTable
{
    use GroupColumn;

    public static function build()
    {
        return [
            self::getName(),
            self::getUsers(),
            self::getPermissions(),
            self::getLabels(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

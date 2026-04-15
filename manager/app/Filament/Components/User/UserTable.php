<?php

namespace App\Filament\Components\User;

use App\Traits\Filament\Specifics\User\UserColumn;

class UserTable
{
    use UserColumn;

    public static function build()
    {
        return [
            self::getEmail(),
            self::getIsVerified(),
            self::getIsRoot(),
            self::getIsActivated(),
            self::getPermissions(),
            self::getGroups(),
            self::getLabels(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

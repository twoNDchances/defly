<?php

namespace App\Filament\Components\User;

use App\Traits\Filament\Specifics\User\UserColumn;

class UserTable
{
    use UserColumn;

    public static function build()
    {
        return [
            self::email(),
            self::isVerified(),
            self::isRoot(),
            self::isActivated(),
            self::permissions(),
            self::policies(),
            self::labels(),
            self::createdBy(),
            self::createdAt(),
            self::updatedAt(),
        ];
    }
}

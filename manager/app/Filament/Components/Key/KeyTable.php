<?php

namespace App\Filament\Components\Key;

use App\Traits\Filament\Specifics\Key\KeyColumn;

class KeyTable
{
    use KeyColumn;

    public static function build()
    {
        return [
            self::getName(),
            self::getExpiredAt(),
            self::getIsReused(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

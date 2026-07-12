<?php

namespace App\Filament\Components\Guard;

use App\Traits\Filament\Specifics\Guard\GuardColumn;

class GuardTable
{
    use GuardColumn;

    public static function build(): array
    {
        return [
            self::getName(),
            self::getExpiredAt(),
            self::getDefenders(),
            self::getUsers(),
            self::getLabels(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

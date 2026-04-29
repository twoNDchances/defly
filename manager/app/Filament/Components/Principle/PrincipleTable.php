<?php

namespace App\Filament\Components\Principle;

use App\Traits\Filament\Specifics\Principle\PrincipleColumn;

class PrincipleTable
{
    use PrincipleColumn;

    public static function build()
    {
        return [
            self::getName(),
            self::getLevel(),
            self::getPhase(),
            self::getValidationStatus(),
            self::getRules(),
            self::getDefenders(),
            self::getLabels(),
            self::getIsLocked(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

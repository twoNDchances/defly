<?php

namespace App\Filament\Components\Decision;

use App\Traits\Filament\Specifics\Decision\DecisionColumn;

class DecisionTable
{
    use DecisionColumn;

    public static function build()
    {
        return [
            self::getName(),
            self::getDirection(),
            self::getCondition(),
            self::getScore(),
            self::getAction(),
            self::getDefenders(),
            self::getLabels(),
            self::getIsLocked(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

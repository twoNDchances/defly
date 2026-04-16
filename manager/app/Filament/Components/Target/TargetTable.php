<?php

namespace App\Filament\Components\Target;

use App\Traits\Filament\Specifics\Target\TargetColumn;

class TargetTable
{
    use TargetColumn;

    public static function build()
    {
        return [
            self::getName(),
            self::getPhase(),
            self::getType(),
            self::getDatatype(),
            self::getPattern(),
            self::getWordlist(),
            self::getEngines(),
            self::getLabels(),
            self::getLocked(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

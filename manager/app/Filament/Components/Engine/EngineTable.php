<?php

namespace App\Filament\Components\Engine;

use App\Traits\Filament\Specifics\Engine\EngineColumn;

class EngineTable
{
    use EngineColumn;

    public static function build()
    {
        return [
            self::getName(),
            self::getInputDatatype(),
            self::getType(),
            self::getOutputDatatype(),
            self::getTargets(),
            self::getLabels(),
            self::getLocked(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

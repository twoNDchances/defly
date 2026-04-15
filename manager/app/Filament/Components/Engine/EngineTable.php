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
            self::getLabels(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

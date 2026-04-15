<?php

namespace App\Filament\Components\Pattern;

use App\Traits\Filament\Specifics\Pattern\PatternColumn;

class PatternTable
{
    use PatternColumn;

    public static function build()
    {
        return [
            self::getName(),
            self::getPhase(),
            self::getType(),
            self::getDatatype(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

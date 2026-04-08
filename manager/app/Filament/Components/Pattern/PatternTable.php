<?php

namespace App\Filament\Components\Pattern;

use App\Traits\Filament\Specifics\Pattern\PatternColumn;

class PatternTable
{
    use PatternColumn;

    public static function build()
    {
        return [
            self::name(),
            self::phase(),
            self::type(),
            self::datatype(),
            self::createdAt(),
            self::updatedAt(),
        ];
    }
}

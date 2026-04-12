<?php

namespace App\Filament\Components\Target;

use App\Traits\Filament\Specifics\Target\TargetColumn;

class TargetTable
{
    use TargetColumn;

    public static function build()
    {
        return [
            self::name(),
            self::phase(),
            self::type(),
            self::datatype(),
            self::pattern(),
            self::wordlist(),
            self::labels(),
            self::createdBy(),
            self::createdAt(),
            self::updatedAt(),
        ];
    }
}

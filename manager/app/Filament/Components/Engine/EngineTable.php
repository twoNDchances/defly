<?php

namespace App\Filament\Components\Engine;

use App\Traits\Filament\Specifics\Engine\EngineColumn;

class EngineTable
{
    use EngineColumn;

    public static function build()
    {
        return [
            self::name(),
            self::inputDatatype(),
            self::type(),
            self::outputDatatype(),
            self::labels(),
            self::createdBy(),
            self::createdAt(),
            self::updatedAt(),
        ];
    }
}

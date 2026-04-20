<?php

namespace App\Filament\Components\Action;

use App\Traits\Filament\Specifics\Action\ActionColumn;

class ActionTable
{
    use ActionColumn;

    public static function build()
    {
        return [
            self::getName(),
            self::getType(),
            self::getRules(),
            self::getLabels(),
            self::getLocked(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

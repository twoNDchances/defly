<?php

namespace App\Filament\Components\Label;

use App\Traits\Filament\Specifics\Label\LabelColumn;

class LabelTable
{
    use LabelColumn;

    public static function build()
    {
        return [
            self::getName(),
            self::getColor(),
            self::getPreview(),
            // self::getUsers(),
            // self::getGroups(),
            // self::getPermissions(),
            // self::getWordlists(),
            // self::getEngines(),
            // self::getTarget(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

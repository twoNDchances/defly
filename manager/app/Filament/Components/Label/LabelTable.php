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
        ];
    }
}

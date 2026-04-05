<?php

namespace App\Filament\Components\Label;

use App\Traits\Filament\Specifics\Label\LabelColumn;

class LabelTable
{
    use LabelColumn;

    public static function build()
    {
        return [
            self::name(),
            self::color(),
            self::preview(),
        ];
    }
}

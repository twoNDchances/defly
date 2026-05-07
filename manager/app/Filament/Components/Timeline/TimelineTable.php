<?php

namespace App\Filament\Components\Timeline;

use App\Traits\Filament\Specifics\Timeline\TimelineColumn;

class TimelineTable
{
    use TimelineColumn;

    public static function build()
    {
        return [
            self::getLoggedAt(),
            self::getCreatedBy(),
            self::getMethod(),
            self::getPath(),
            self::getAction(),
            self::getResource(),
        ];
    }
}

<?php

namespace App\Filament\Components\Timeline;

use App\Traits\Filament\Specifics\Timeline\TimelineColumn;
use App\Traits\Filament\Specifics\Timeline\TimelineFilter;

class TimelineTable
{
    use TimelineColumn;
    use TimelineFilter;

    public static function build()
    {
        return [
            self::getResource(),
            self::getAction(),
            self::getMethod(),
            self::getPath(),
            self::getCreatedBy(),
            self::getLoggedAt(),
        ];
    }
}

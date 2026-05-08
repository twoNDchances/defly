<?php

namespace App\Filament\Components\Timeline;

use App\Traits\Filament\Specifics\Timeline\TimelineColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;

class TimelineTable
{
    use TimelineColumn;

    public static function build()
    {
        return [
            Stack::make([
                self::getResource()->weight('bold')->size('lg'),
                Split::make([
                    self::getAction(),
                    self::getMethod(),
                    self::getPath(),
                ]),
            ]),
            Stack::make([
                Split::make([
                    self::getCreatedBy(),
                    self::getLoggedAt(),
                ]),
            ])
            ->alignEnd(),
        ];
    }
}

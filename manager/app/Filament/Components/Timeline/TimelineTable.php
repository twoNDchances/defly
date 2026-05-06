<?php

namespace App\Filament\Components\Timeline;

use App\Traits\Filament\Specifics\Timeline\TimelineColumn;
use Devletes\FilamentTimelineView\Tables\Columns\TimelineEntry;

class TimelineTable
{
    use TimelineColumn;

    public static function build()
    {
        return [
            self::getTimeline(),
        ];
    }
}

<?php

namespace App\Traits\Filament\Specifics\Timeline;

use App\Traits\Filament\Generals\Components\Column;
use Devletes\FilamentTimelineView\Tables\Columns\TimelineEntry;

trait TimelineColumn
{
    use Column, TimelineButton, TimelineData;

    public static function getTimeline()
    {
        return TimelineEntry::make()
        ->title('resource_type')
        ->content('action')
        ->author('createdBy.email')
        ->time('created_at');
    }
}

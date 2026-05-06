<?php

namespace App\Filament\Resources\Timelines\Pages;

use App\Filament\Resources\Timelines\TimelineResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListTimelines extends ListRecords
{
    use ListPage;

    protected static string $resource = TimelineResource::class;
}

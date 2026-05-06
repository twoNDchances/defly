<?php

namespace App\Filament\Resources\Timelines\Pages;

use App\Filament\Resources\Timelines\TimelineResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use Filament\Resources\Pages\CreateRecord;

class CreateTimeline extends CreateRecord
{
    use CreatePage;

    protected static string $resource = TimelineResource::class;
}

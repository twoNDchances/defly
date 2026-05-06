<?php

namespace App\Filament\Resources\Timelines\Pages;

use App\Filament\Resources\Timelines\TimelineResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use Filament\Resources\Pages\EditRecord;

class EditTimeline extends EditRecord
{
    use EditPage;

    protected static string $resource = TimelineResource::class;
}

<?php

namespace App\Filament\Resources\Labels\Pages;

use App\Filament\Resources\Labels\LabelResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use Filament\Resources\Pages\CreateRecord;

class CreateLabel extends CreateRecord
{
    use CreatePage;

    protected static string $resource = LabelResource::class;
}

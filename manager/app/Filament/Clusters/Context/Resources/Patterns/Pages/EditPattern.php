<?php

namespace App\Filament\Clusters\Context\Resources\Patterns\Pages;

use App\Filament\Clusters\Context\Resources\Patterns\PatternResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use Filament\Resources\Pages\EditRecord;

class EditPattern extends EditRecord
{
    use EditPage;

    protected static string $resource = PatternResource::class;
}

<?php

namespace App\Filament\Clusters\Context\Resources\Patterns\Pages;

use App\Filament\Clusters\Context\Resources\Patterns\PatternResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use Filament\Resources\Pages\CreateRecord;

class CreatePattern extends CreateRecord
{
    use CreatePage;

    protected static string $resource = PatternResource::class;
}

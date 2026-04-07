<?php

namespace App\Filament\Clusters\Context\Resources\Engines\Pages;

use App\Filament\Clusters\Context\Resources\Engines\EngineResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListEngines extends ListRecords
{
    use ListPage;

    protected static string $resource = EngineResource::class;
}

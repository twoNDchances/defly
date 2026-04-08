<?php

namespace App\Filament\Clusters\Context\Resources\Patterns\Pages;

use App\Filament\Clusters\Context\Resources\Patterns\PatternResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListPatterns extends ListRecords
{
    use ListPage;

    protected static string $resource = PatternResource::class;
}

<?php

namespace App\Filament\Clusters\Initialization\Resources\Decisions\Pages;

use App\Filament\Clusters\Initialization\Resources\Decisions\DecisionResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListDecisions extends ListRecords
{
    use ListPage;

    protected static string $resource = DecisionResource::class;
}

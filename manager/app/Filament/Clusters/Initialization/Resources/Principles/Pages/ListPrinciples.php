<?php

namespace App\Filament\Clusters\Initialization\Resources\Principles\Pages;

use App\Filament\Clusters\Initialization\Resources\Principles\PrincipleResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListPrinciples extends ListRecords
{
    use ListPage;

    protected static string $resource = PrincipleResource::class;
}

<?php

namespace App\Filament\Clusters\Initialization\Resources\Policies\Pages;

use App\Filament\Clusters\Initialization\Resources\Policies\PolicyResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListPolicies extends ListRecords
{
    use ListPage;

    protected static string $resource = PolicyResource::class;
}

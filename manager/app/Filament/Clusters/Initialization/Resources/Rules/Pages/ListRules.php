<?php

namespace App\Filament\Clusters\Initialization\Resources\Rules\Pages;

use App\Filament\Clusters\Initialization\Resources\Rules\RuleResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListRules extends ListRecords
{
    use ListPage;

    protected static string $resource = RuleResource::class;
}

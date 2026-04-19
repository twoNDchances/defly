<?php

namespace App\Filament\Clusters\Initialization\Resources\Actions\Pages;

use App\Filament\Clusters\Initialization\Resources\Actions\ActionResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListActions extends ListRecords
{
    use ListPage;

    protected static string $resource = ActionResource::class;
}

<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Guards\Pages;

use App\Filament\Clusters\Infrastructure\Resources\Guards\GuardResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListGuards extends ListRecords
{
    use ListPage;

    protected static string $resource = GuardResource::class;
}

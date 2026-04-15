<?php

namespace App\Filament\Clusters\AccessControl\Resources\Groups\Pages;

use App\Filament\Clusters\AccessControl\Resources\Groups\GroupResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListGroups extends ListRecords
{
    use ListPage;

    protected static string $resource = GroupResource::class;
}

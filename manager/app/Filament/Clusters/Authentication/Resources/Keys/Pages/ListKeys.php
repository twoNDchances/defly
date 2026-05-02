<?php

namespace App\Filament\Clusters\Authentication\Resources\Keys\Pages;

use App\Filament\Clusters\Authentication\Resources\Keys\KeyResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListKeys extends ListRecords
{
    use ListPage;

    protected static string $resource = KeyResource::class;
}

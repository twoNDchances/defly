<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Defenders\Pages;

use App\Filament\Clusters\Infrastructure\Resources\Defenders\DefenderResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListDefenders extends ListRecords
{
    use ListPage;

    protected static string $resource = DefenderResource::class;
}

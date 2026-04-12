<?php

namespace App\Filament\Clusters\Context\Resources\Targets\Pages;

use App\Filament\Clusters\Context\Resources\Targets\TargetResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListTargets extends ListRecords
{
    use ListPage;

    protected static string $resource = TargetResource::class;
}

<?php

namespace App\Filament\Clusters\Initialization\Resources\Principles\Pages;

use App\Filament\Clusters\Initialization\Resources\Principles\PrincipleResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use Filament\Resources\Pages\CreateRecord;

class CreatePrinciple extends CreateRecord
{
    use CreatePage;

    protected static string $resource = PrincipleResource::class;
}

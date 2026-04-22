<?php

namespace App\Filament\Clusters\Initialization\Resources\Policies\Pages;

use App\Filament\Clusters\Initialization\Resources\Policies\PolicyResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use Filament\Resources\Pages\CreateRecord;

class CreatePolicy extends CreateRecord
{
    use CreatePage;

    protected static string $resource = PolicyResource::class;
}

<?php

namespace App\Filament\Clusters\AccessControl\Resources\Policies\Pages;

use App\Filament\Clusters\AccessControl\Resources\Policies\PolicyResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePolicy extends CreateRecord
{
    protected static string $resource = PolicyResource::class;
}

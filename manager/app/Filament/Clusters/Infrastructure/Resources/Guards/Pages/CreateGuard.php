<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Guards\Pages;

use App\Filament\Clusters\Infrastructure\Resources\Guards\GuardResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use Filament\Resources\Pages\CreateRecord;

class CreateGuard extends CreateRecord
{
    use CreatePage;

    protected static string $resource = GuardResource::class;
}

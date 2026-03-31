<?php

namespace App\Filament\Clusters\AccessControl\Resources\Policies\Pages;

use App\Filament\Clusters\AccessControl\Resources\Policies\PolicyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPolicies extends ListRecords
{
    protected static string $resource = PolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

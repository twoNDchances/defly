<?php

namespace App\Filament\Clusters\AccessControl\Resources\Policies\Pages;

use App\Filament\Clusters\AccessControl\Resources\Policies\PolicyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPolicy extends EditRecord
{
    protected static string $resource = PolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Clusters\Initialization\Resources\Decisions\Pages;

use App\Filament\Clusters\Initialization\Resources\Decisions\DecisionResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use App\Traits\Filament\Specifics\Decision\DecisionData;
use Filament\Resources\Pages\CreateRecord;

class CreateDecision extends CreateRecord
{
    use CreatePage, DecisionData;

    protected static string $resource = DecisionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return self::saveForm($data);
    }
}

<?php

namespace App\Filament\Clusters\Initialization\Resources\Rules\Pages;

use App\Filament\Clusters\Initialization\Resources\Rules\RuleResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use App\Traits\Filament\Specifics\Rule\RuleData;
use Filament\Resources\Pages\CreateRecord;

class CreateRule extends CreateRecord
{
    use CreatePage, RuleData;

    protected static string $resource = RuleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return self::saveForm($data);
    }
}

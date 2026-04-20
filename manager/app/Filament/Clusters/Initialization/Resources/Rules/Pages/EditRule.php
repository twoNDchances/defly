<?php

namespace App\Filament\Clusters\Initialization\Resources\Rules\Pages;

use App\Filament\Clusters\Initialization\Resources\Rules\RuleResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use App\Traits\Filament\Specifics\Rule\RuleData;
use Filament\Resources\Pages\EditRecord;

class EditRule extends EditRecord
{
    use EditPage, RuleData;

    protected static string $resource = RuleResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return self::loadForm($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return self::saveForm($data);
    }
}

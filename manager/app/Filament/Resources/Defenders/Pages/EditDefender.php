<?php

namespace App\Filament\Resources\Defenders\Pages;

use App\Filament\Resources\Defenders\DefenderResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use App\Traits\Filament\Specifics\Defender\DefenderData;
use Filament\Resources\Pages\EditRecord;

class EditDefender extends EditRecord
{
    use DefenderData, EditPage;

    protected static string $resource = DefenderResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return self::loadForm($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return self::saveForm($data);
    }
}

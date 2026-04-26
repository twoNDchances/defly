<?php

namespace App\Filament\Resources\Defenders\Pages;

use App\Filament\Resources\Defenders\DefenderResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use App\Traits\Filament\Specifics\Defender\DefenderData;
use Filament\Resources\Pages\CreateRecord;

class CreateDefender extends CreateRecord
{
    use CreatePage, DefenderData;

    protected static string $resource = DefenderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return self::saveForm($data);
    }
}

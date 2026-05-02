<?php

namespace App\Filament\Clusters\Authentication\Resources\Keys\Pages;

use App\Filament\Clusters\Authentication\Resources\Keys\KeyResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use App\Traits\Filament\Specifics\Key\KeyData;
use Filament\Resources\Pages\EditRecord;

class EditKey extends EditRecord
{
    use EditPage, KeyData;

    protected static string $resource = KeyResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return self::editForm($data);
    }
}

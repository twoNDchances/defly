<?php

namespace App\Filament\Clusters\Context\Resources\Engines\Pages;

use App\Filament\Clusters\Context\Resources\Engines\EngineResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use App\Traits\Filament\Generals\Pages\Navigations\RedirectListPage;
use App\Traits\Filament\Specifics\Engine\EngineData;
use Filament\Resources\Pages\EditRecord;

class EditEngine extends EditRecord
{
    use EditPage, EngineData, RedirectListPage;

    protected static string $resource = EngineResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return self::loadForm($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return self::saveForm($data);
    }
}

<?php

namespace App\Filament\Clusters\Context\Resources\Engines\Pages;

use App\Filament\Clusters\Context\Resources\Engines\EngineResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use App\Traits\Filament\Generals\Pages\Navigations\RedirectListPage;
use App\Traits\Filament\Specifics\Engine\EngineData;
use Filament\Resources\Pages\CreateRecord;

class CreateEngine extends CreateRecord
{
    use CreatePage, EngineData, RedirectListPage;

    protected static string $resource = EngineResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return self::saveForm($data);
    }
}

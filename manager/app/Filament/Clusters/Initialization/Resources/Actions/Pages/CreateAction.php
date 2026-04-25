<?php

namespace App\Filament\Clusters\Initialization\Resources\Actions\Pages;

use App\Filament\Clusters\Initialization\Resources\Actions\ActionResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use App\Traits\Filament\Generals\Pages\Navigations\RedirectListPage;
use App\Traits\Filament\Specifics\Action\ActionData;
use Filament\Resources\Pages\CreateRecord;

class CreateAction extends CreateRecord
{
    use ActionData, CreatePage, RedirectListPage;

    protected static string $resource = ActionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return self::saveForm($data);
    }
}

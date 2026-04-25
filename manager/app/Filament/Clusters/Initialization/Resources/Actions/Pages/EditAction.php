<?php

namespace App\Filament\Clusters\Initialization\Resources\Actions\Pages;

use App\Filament\Clusters\Initialization\Resources\Actions\ActionResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use App\Traits\Filament\Generals\Pages\Navigations\RedirectListPage;
use App\Traits\Filament\Specifics\Action\ActionData;
use Filament\Resources\Pages\EditRecord;

class EditAction extends EditRecord
{
    use ActionData, EditPage, RedirectListPage;

    protected static string $resource = ActionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return self::loadForm($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return self::saveForm($data);
    }
}

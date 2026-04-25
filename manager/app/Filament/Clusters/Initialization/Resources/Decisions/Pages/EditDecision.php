<?php

namespace App\Filament\Clusters\Initialization\Resources\Decisions\Pages;

use App\Filament\Clusters\Initialization\Resources\Decisions\DecisionResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use App\Traits\Filament\Generals\Pages\Navigations\RedirectListPage;
use App\Traits\Filament\Specifics\Decision\DecisionData;
use Filament\Resources\Pages\EditRecord;

class EditDecision extends EditRecord
{
    use DecisionData, EditPage, RedirectListPage;

    protected static string $resource = DecisionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return self::loadForm($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return self::saveForm($data);
    }
}

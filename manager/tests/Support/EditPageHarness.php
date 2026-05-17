<?php

namespace Tests\Support;

use App\Traits\Filament\Generals\Pages\EditPage;

class EditPageHarness
{
    use EditPage;

    public array $refreshed = [];

    public function refreshFormData(array $statePaths)
    {
        $this->refreshed = $statePaths;
    }

    public function headerActionsPublic(): array
    {
        return $this->getHeaderActions();
    }

    public function beforeFillPublic(array $data): array
    {
        return $this->mutateFormDataBeforeFill($data);
    }

    public function beforeSavePublic(array $data): array
    {
        return $this->mutateFormDataBeforeSave($data);
    }
}

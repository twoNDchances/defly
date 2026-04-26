<?php

namespace App\Traits\Filament\Generals\Pages;

use App\Traits\Filament\Generals\Components\Button;
use Livewire\Attributes\On;

trait EditPage
{
    use Button;

    #[On('refresh-form-data')]
    public function refreshFormDataFromRelationManager(array $statePaths = []): void
    {
        if ($statePaths === []) {
            return;
        }

        $this->refreshFormData($statePaths);
    }

    protected function getHeaderActions(): array
    {
        return [
            self::deleteButton(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }
}

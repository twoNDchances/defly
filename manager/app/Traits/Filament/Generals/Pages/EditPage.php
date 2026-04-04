<?php

namespace App\Traits\Filament\Generals\Pages;

use App\Traits\Filament\Generals\Components\Button;

trait EditPage
{
    use Button;

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

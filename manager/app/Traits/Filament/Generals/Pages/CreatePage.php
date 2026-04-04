<?php

namespace App\Traits\Filament\Generals\Pages;

trait CreatePage
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }
}

<?php

namespace App\Traits\Filament\Pages;

use Filament\Actions\CreateAction;

trait ListPage
{
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Traits\Filament\Generals\Pages;

use App\Traits\Filament\Generals\Components\Button;

trait ListPage
{
    use Button;

    protected function getHeaderActions(): array
    {
        return [
            self::createButton(),
        ];
    }
}

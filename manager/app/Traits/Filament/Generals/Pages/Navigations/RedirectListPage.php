<?php

namespace App\Traits\Filament\Generals\Pages\Navigations;

trait RedirectListPage
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

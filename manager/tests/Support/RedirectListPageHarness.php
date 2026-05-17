<?php

namespace Tests\Support;

use App\Filament\Resources\Labels\LabelResource;
use App\Traits\Filament\Generals\Pages\Navigations\RedirectListPage;

class RedirectListPageHarness
{
    use RedirectListPage;

    public function getResource(): string
    {
        return LabelResource::class;
    }

    public function redirectUrlPublic(): string
    {
        return $this->getRedirectUrl();
    }
}

<?php

namespace App\Filament\Resources\Wordlists\Pages;

use App\Filament\Resources\Wordlists\WordlistResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use App\Traits\Filament\Generals\Pages\Navigations\RedirectListPage;
use Filament\Resources\Pages\CreateRecord;

class CreateWordlist extends CreateRecord
{
    use CreatePage, RedirectListPage;

    protected static string $resource = WordlistResource::class;
}

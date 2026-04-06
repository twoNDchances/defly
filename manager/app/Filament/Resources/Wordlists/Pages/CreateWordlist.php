<?php

namespace App\Filament\Resources\Wordlists\Pages;

use App\Filament\Resources\Wordlists\WordlistResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use Filament\Resources\Pages\CreateRecord;

class CreateWordlist extends CreateRecord
{
    use CreatePage;

    protected static string $resource = WordlistResource::class;
}

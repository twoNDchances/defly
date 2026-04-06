<?php

namespace App\Filament\Resources\Wordlists\Pages;

use App\Filament\Resources\Wordlists\WordlistResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use Filament\Resources\Pages\EditRecord;

class EditWordlist extends EditRecord
{
    use EditPage;

    protected static string $resource = WordlistResource::class;
}

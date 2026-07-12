<?php

namespace App\Filament\Clusters\Context\Resources\Wordlists\Pages;

use App\Filament\Clusters\Context\Resources\Wordlists\WordlistResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListWordlists extends ListRecords
{
    use ListPage;

    protected static string $resource = WordlistResource::class;
}

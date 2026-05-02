<?php

namespace App\Filament\Clusters\Authentication\Resources\Keys\Pages;

use App\Filament\Clusters\Authentication\Resources\Keys\KeyResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use Filament\Resources\Pages\CreateRecord;

class CreateKey extends CreateRecord
{
    use CreatePage;

    protected static string $resource = KeyResource::class;
}

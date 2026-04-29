<?php

namespace App\Filament\Clusters\Initialization\Resources\Principles\Pages;

use App\Filament\Clusters\Initialization\Resources\Principles\PrincipleResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use Filament\Resources\Pages\EditRecord;

class EditPrinciple extends EditRecord
{
    use EditPage;

    protected static string $resource = PrincipleResource::class;
}

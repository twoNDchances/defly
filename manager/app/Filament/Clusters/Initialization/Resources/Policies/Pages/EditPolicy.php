<?php

namespace App\Filament\Clusters\Initialization\Resources\Policies\Pages;

use App\Filament\Clusters\Initialization\Resources\Policies\PolicyResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use Filament\Resources\Pages\EditRecord;

class EditPolicy extends EditRecord
{
    use EditPage;

    protected static string $resource = PolicyResource::class;
}

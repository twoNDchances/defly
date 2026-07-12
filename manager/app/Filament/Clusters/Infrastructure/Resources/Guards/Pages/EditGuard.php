<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Guards\Pages;

use App\Filament\Clusters\Infrastructure\Resources\Guards\GuardResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use Filament\Resources\Pages\EditRecord;

class EditGuard extends EditRecord
{
    use EditPage;

    protected static string $resource = GuardResource::class;
}

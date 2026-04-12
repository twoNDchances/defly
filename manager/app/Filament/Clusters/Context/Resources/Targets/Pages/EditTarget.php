<?php

namespace App\Filament\Clusters\Context\Resources\Targets\Pages;

use App\Filament\Clusters\Context\Resources\Targets\TargetResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use Filament\Resources\Pages\EditRecord;

class EditTarget extends EditRecord
{
    use EditPage;

    protected static string $resource = TargetResource::class;
}

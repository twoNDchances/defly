<?php

namespace App\Filament\Clusters\Context\Resources\Targets\Pages;

use App\Filament\Clusters\Context\Resources\Targets\TargetResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use Filament\Resources\Pages\CreateRecord;

class CreateTarget extends CreateRecord
{
    use CreatePage;

    protected static string $resource = TargetResource::class;
}

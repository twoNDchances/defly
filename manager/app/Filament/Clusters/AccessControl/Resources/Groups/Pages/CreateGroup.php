<?php

namespace App\Filament\Clusters\AccessControl\Resources\Groups\Pages;

use App\Filament\Clusters\AccessControl\Resources\Groups\GroupResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use Filament\Resources\Pages\CreateRecord;

class CreateGroup extends CreateRecord
{
    use CreatePage;

    protected static string $resource = GroupResource::class;
}

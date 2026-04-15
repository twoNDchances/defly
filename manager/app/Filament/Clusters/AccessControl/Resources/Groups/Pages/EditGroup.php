<?php

namespace App\Filament\Clusters\AccessControl\Resources\Groups\Pages;

use App\Filament\Clusters\AccessControl\Resources\Groups\GroupResource;
use App\Traits\Filament\Generals\Pages\EditPage;
use Filament\Resources\Pages\EditRecord;

class EditGroup extends EditRecord
{
    use EditPage;

    protected static string $resource = GroupResource::class;
}

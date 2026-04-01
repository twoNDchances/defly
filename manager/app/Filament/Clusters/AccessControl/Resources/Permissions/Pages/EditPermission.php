<?php

namespace App\Filament\Clusters\AccessControl\Resources\Permissions\Pages;

use App\Filament\Clusters\AccessControl\Resources\Permissions\PermissionResource;
use App\Traits\Filament\Pages\EditPage;
use Filament\Resources\Pages\EditRecord;

class EditPermission extends EditRecord
{
    use EditPage;

    protected static string $resource = PermissionResource::class;
}

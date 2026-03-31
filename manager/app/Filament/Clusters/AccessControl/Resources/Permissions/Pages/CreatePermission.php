<?php

namespace App\Filament\Clusters\AccessControl\Resources\Permissions\Pages;

use App\Filament\Clusters\AccessControl\Resources\Permissions\PermissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;
}

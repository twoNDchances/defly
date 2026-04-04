<?php

namespace App\Filament\Clusters\AccessControl\Resources\Permissions\Pages;

use App\Filament\Clusters\AccessControl\Resources\Permissions\PermissionResource;
use App\Traits\Filament\Generals\Pages\CreatePage;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    use CreatePage;

    protected static string $resource = PermissionResource::class;
}

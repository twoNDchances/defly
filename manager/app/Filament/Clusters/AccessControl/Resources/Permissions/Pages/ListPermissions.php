<?php

namespace App\Filament\Clusters\AccessControl\Resources\Permissions\Pages;

use App\Filament\Clusters\AccessControl\Resources\Permissions\PermissionResource;
use App\Traits\Filament\Generals\Pages\ListPage;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    use ListPage;

    protected static string $resource = PermissionResource::class;
}

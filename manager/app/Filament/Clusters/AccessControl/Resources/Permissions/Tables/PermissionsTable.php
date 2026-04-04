<?php

namespace App\Filament\Clusters\AccessControl\Resources\Permissions\Tables;

use App\Models\Permission;
use App\Traits\Filament\Specifics\Permission\PermissionButton;
use App\Traits\Filament\Specifics\Permission\PermissionColumn;
use Filament\Tables\Table;

class PermissionsTable
{
    use PermissionButton;
    use PermissionColumn;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                self::name(),
                self::appliedFor(),
                self::action(),
                self::canManageFromOther(),
                self::createdBy(),
                self::createdAt(),
                self::updatedAt(),
            ])
            ->query(Permission::query()->manage())
            ->filters([
                //
            ])
            ->recordActions([
                self::buttonGroup(),
            ])
            ->toolbarActions([
                self::bulkButtonGroup(),
            ]);
    }
}

<?php

namespace App\Filament\Clusters\AccessControl\Resources\Permissions\Tables;

use App\Filament\Components\Permission\PermissionTable;
use Filament\Tables\Table;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(PermissionTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                PermissionTable::buttonGroup(),
            ])
            ->toolbarActions([
                PermissionTable::bulkButtonGroup(),
            ]);
    }
}

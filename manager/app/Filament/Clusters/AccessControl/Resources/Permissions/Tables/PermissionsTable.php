<?php

namespace App\Filament\Clusters\AccessControl\Resources\Permissions\Tables;

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
            ->columns(self::columns())
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

    public static function columns()
    {
        return [
            self::name(),
            self::appliedFor(),
            self::action(),
            self::users(),
            self::policies(),
            self::labels(),
            self::createdBy(),
            self::createdAt(),
            self::updatedAt(),
        ];
    }
}

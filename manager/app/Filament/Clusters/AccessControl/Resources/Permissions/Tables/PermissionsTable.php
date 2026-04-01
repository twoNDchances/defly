<?php

namespace App\Filament\Clusters\AccessControl\Resources\Permissions\Tables;

use App\Models\Permission;
use App\Services\Security;
use App\Traits\Filament\Button;
use App\Traits\Filament\Columns\PermissionColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermissionsTable
{
    use PermissionColumn;
    use Button;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                self::name(),
                self::appliedFor(),
                self::action(),
                self::createdBy(),
                self::createdAt(),
                self::updatedAt(),
            ])
            ->query(fn () => Security::viewAnyOther(Permission::class))
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

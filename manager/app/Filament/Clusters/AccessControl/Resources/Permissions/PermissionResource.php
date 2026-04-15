<?php

namespace App\Filament\Clusters\AccessControl\Resources\Permissions;

use App\Filament\Clusters\AccessControl\AccessControlCluster;
use App\Filament\Clusters\AccessControl\Resources\Permissions\Pages\CreatePermission;
use App\Filament\Clusters\AccessControl\Resources\Permissions\Pages\EditPermission;
use App\Filament\Clusters\AccessControl\Resources\Permissions\Pages\ListPermissions;
use App\Filament\Clusters\AccessControl\Resources\Permissions\Schemas\PermissionForm;
use App\Filament\Clusters\AccessControl\Resources\Permissions\Tables\PermissionsTable;
use App\Filament\Components\Group\GroupRelationManager;
use App\Filament\Components\User\UserRelationManager;
use App\Models\Permission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockOpen;

    protected static ?string $cluster = AccessControlCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            UserRelationManager::class,
            GroupRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('models.permission.name');
    }
}

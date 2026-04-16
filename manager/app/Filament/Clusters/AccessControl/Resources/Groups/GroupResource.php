<?php

namespace App\Filament\Clusters\AccessControl\Resources\Groups;

use App\Filament\Clusters\AccessControl\AccessControlCluster;
use App\Filament\Clusters\AccessControl\Resources\Groups\Pages\CreateGroup;
use App\Filament\Clusters\AccessControl\Resources\Groups\Pages\EditGroup;
use App\Filament\Clusters\AccessControl\Resources\Groups\Pages\ListGroups;
use App\Filament\Clusters\AccessControl\Resources\Groups\RelationManagers\PermissionsRelationManager;
use App\Filament\Clusters\AccessControl\Resources\Groups\RelationManagers\UsersRelationManager;
use App\Filament\Clusters\AccessControl\Resources\Groups\Schemas\GroupForm;
use App\Filament\Clusters\AccessControl\Resources\Groups\Tables\GroupsTable;
use App\Models\Group;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $cluster = AccessControlCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return GroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
            PermissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGroups::route('/'),
            'create' => CreateGroup::route('/create'),
            'edit' => EditGroup::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('models.group.name');
    }
}

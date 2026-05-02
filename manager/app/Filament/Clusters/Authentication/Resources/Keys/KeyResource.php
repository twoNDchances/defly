<?php

namespace App\Filament\Clusters\Authentication\Resources\Keys;

use App\Filament\Clusters\Authentication\AuthenticationCluster;
use App\Filament\Clusters\Authentication\Resources\Keys\Pages\CreateKey;
use App\Filament\Clusters\Authentication\Resources\Keys\Pages\EditKey;
use App\Filament\Clusters\Authentication\Resources\Keys\Pages\ListKeys;
use App\Filament\Clusters\Authentication\Resources\Keys\RelationManagers\GroupsRelationManager;
use App\Filament\Clusters\Authentication\Resources\Keys\RelationManagers\PermissionsRelationManager;
use App\Filament\Clusters\Authentication\Resources\Keys\Schemas\KeyForm;
use App\Filament\Clusters\Authentication\Resources\Keys\Tables\KeysTable;
use App\Models\Key;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KeyResource extends Resource
{
    protected static ?string $model = Key::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $cluster = AuthenticationCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return KeyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KeysTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PermissionsRelationManager::class,
            GroupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKeys::route('/'),
            'create' => CreateKey::route('/create'),
            'edit' => EditKey::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('models.key.name');
    }
}

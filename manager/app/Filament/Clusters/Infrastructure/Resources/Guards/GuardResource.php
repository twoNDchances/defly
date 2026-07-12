<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Guards;

use App\Filament\Clusters\Infrastructure\InfrastructureCluster;
use App\Filament\Clusters\Infrastructure\Resources\Guards\Pages\CreateGuard;
use App\Filament\Clusters\Infrastructure\Resources\Guards\Pages\EditGuard;
use App\Filament\Clusters\Infrastructure\Resources\Guards\Pages\ListGuards;
use App\Filament\Clusters\Infrastructure\Resources\Guards\RelationManagers\DefendersRelationManager;
use App\Filament\Clusters\Infrastructure\Resources\Guards\RelationManagers\UsersRelationManager;
use App\Filament\Clusters\Infrastructure\Resources\Guards\Schemas\GuardForm;
use App\Filament\Clusters\Infrastructure\Resources\Guards\Tables\GuardsTable;
use App\Models\Guard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GuardResource extends Resource
{
    protected static ?string $model = Guard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShieldCheck;

    protected static ?string $cluster = InfrastructureCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return GuardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
            DefendersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuards::route('/'),
            'create' => CreateGuard::route('/create'),
            'edit' => EditGuard::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('models.guard.name');
    }
}

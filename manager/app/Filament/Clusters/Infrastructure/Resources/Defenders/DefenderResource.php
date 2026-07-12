<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Defenders;

use App\Filament\Clusters\Infrastructure\InfrastructureCluster;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\Pages\CreateDefender;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\Pages\EditDefender;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\Pages\ListDefenders;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\RelationManagers\DecisionsRelationManager;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\RelationManagers\PrinciplesRelationManager;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\RelationManagers\ReportsRelationManager;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\Schemas\DefenderForm;
use App\Filament\Clusters\Infrastructure\Resources\Defenders\Tables\DefendersTable;
use App\Models\Defender;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DefenderResource extends Resource
{
    protected static ?string $model = Defender::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Server;

    protected static ?string $cluster = InfrastructureCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return DefenderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DefendersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PrinciplesRelationManager::class,
            DecisionsRelationManager::class,
            ReportsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDefenders::route('/'),
            'create' => CreateDefender::route('/create'),
            'edit' => EditDefender::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('models.defender.name');
    }
}

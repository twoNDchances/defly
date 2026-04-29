<?php

namespace App\Filament\Clusters\Initialization\Resources\Principles;

use App\Filament\Clusters\Initialization\InitializationCluster;
use App\Filament\Clusters\Initialization\Resources\Principles\Pages\CreatePrinciple;
use App\Filament\Clusters\Initialization\Resources\Principles\Pages\EditPrinciple;
use App\Filament\Clusters\Initialization\Resources\Principles\Pages\ListPrinciples;
use App\Filament\Clusters\Initialization\Resources\Principles\RelationManagers\RulesRelationManager;
use App\Filament\Clusters\Initialization\Resources\Principles\Schemas\PrincipleForm;
use App\Filament\Clusters\Initialization\Resources\Principles\Tables\PrinciplesTable;
use App\Models\Principle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PrincipleResource extends Resource
{
    protected static ?string $model = Principle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = InitializationCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PrincipleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrinciplesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrinciples::route('/'),
            'create' => CreatePrinciple::route('/create'),
            'edit' => EditPrinciple::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('models.principle.name');
    }
}

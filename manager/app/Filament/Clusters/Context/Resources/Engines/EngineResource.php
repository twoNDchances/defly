<?php

namespace App\Filament\Clusters\Context\Resources\Engines;

use App\Filament\Clusters\Context\ContextCluster;
use App\Filament\Clusters\Context\Resources\Engines\Pages\CreateEngine;
use App\Filament\Clusters\Context\Resources\Engines\Pages\EditEngine;
use App\Filament\Clusters\Context\Resources\Engines\Pages\ListEngines;
use App\Filament\Clusters\Context\Resources\Engines\Schemas\EngineForm;
use App\Filament\Clusters\Context\Resources\Engines\Tables\EnginesTable;
use App\Models\Engine;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EngineResource extends Resource
{
    protected static ?string $model = Engine::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static ?string $cluster = ContextCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return EngineForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnginesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEngines::route('/'),
            'create' => CreateEngine::route('/create'),
            'edit' => EditEngine::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('models.engine.name');
    }
}

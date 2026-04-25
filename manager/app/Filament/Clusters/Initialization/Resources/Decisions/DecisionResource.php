<?php

namespace App\Filament\Clusters\Initialization\Resources\Decisions;

use App\Filament\Clusters\Initialization\InitializationCluster;
use App\Filament\Clusters\Initialization\Resources\Decisions\Pages\CreateDecision;
use App\Filament\Clusters\Initialization\Resources\Decisions\Pages\EditDecision;
use App\Filament\Clusters\Initialization\Resources\Decisions\Pages\ListDecisions;
use App\Filament\Clusters\Initialization\Resources\Decisions\Schemas\DecisionForm;
use App\Filament\Clusters\Initialization\Resources\Decisions\Tables\DecisionsTable;
use App\Models\Decision;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DecisionResource extends Resource
{
    protected static ?string $model = Decision::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?string $cluster = InitializationCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return DecisionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DecisionsTable::configure($table);
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
            'index' => ListDecisions::route('/'),
            'create' => CreateDecision::route('/create'),
            'edit' => EditDecision::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('models.decision.name');
    }
}

<?php

namespace App\Filament\Clusters\Context\Resources\Patterns;

use App\Filament\Clusters\Context\ContextCluster;
use App\Filament\Clusters\Context\Resources\Patterns\Pages\CreatePattern;
use App\Filament\Clusters\Context\Resources\Patterns\Pages\EditPattern;
use App\Filament\Clusters\Context\Resources\Patterns\Pages\ListPatterns;
use App\Filament\Clusters\Context\Resources\Patterns\Schemas\PatternForm;
use App\Filament\Clusters\Context\Resources\Patterns\Tables\PatternsTable;
use App\Models\Pattern;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PatternResource extends Resource
{
    protected static ?string $model = Pattern::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $cluster = ContextCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return PatternForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatternsTable::configure($table);
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
            'index' => ListPatterns::route('/'),
            'create' => CreatePattern::route('/create'),
            'edit' => EditPattern::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('models.pattern.name');
    }
}

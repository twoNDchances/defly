<?php

namespace App\Filament\Clusters\Context\Resources\Targets;

use App\Filament\Clusters\Context\ContextCluster;
use App\Filament\Clusters\Context\Resources\Targets\Pages\CreateTarget;
use App\Filament\Clusters\Context\Resources\Targets\Pages\EditTarget;
use App\Filament\Clusters\Context\Resources\Targets\Pages\ListTargets;
use App\Filament\Clusters\Context\Resources\Targets\RelationManagers\EnginesRelationManager;
use App\Filament\Clusters\Context\Resources\Targets\Schemas\TargetForm;
use App\Filament\Clusters\Context\Resources\Targets\Tables\TargetsTable;
use App\Models\Target;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TargetResource extends Resource
{
    protected static ?string $model = Target::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $cluster = ContextCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return TargetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TargetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            EnginesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTargets::route('/'),
            'create' => CreateTarget::route('/create'),
            'edit' => EditTarget::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('models.target.name');
    }
}

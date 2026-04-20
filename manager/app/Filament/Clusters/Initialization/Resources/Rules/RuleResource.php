<?php

namespace App\Filament\Clusters\Initialization\Resources\Rules;

use App\Filament\Clusters\Initialization\InitializationCluster;
use App\Filament\Clusters\Initialization\Resources\Rules\Pages\CreateRule;
use App\Filament\Clusters\Initialization\Resources\Rules\Pages\EditRule;
use App\Filament\Clusters\Initialization\Resources\Rules\Pages\ListRules;
use App\Filament\Clusters\Initialization\Resources\Rules\RelationManagers\ActionsRelationManager;
use App\Filament\Clusters\Initialization\Resources\Rules\Schemas\RuleForm;
use App\Filament\Clusters\Initialization\Resources\Rules\Tables\RulesTable;
use App\Models\Rule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RuleResource extends Resource
{
    protected static ?string $model = Rule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $cluster = InitializationCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return RuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ActionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRules::route('/'),
            'create' => CreateRule::route('/create'),
            'edit' => EditRule::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('models.rule.name');
    }
}

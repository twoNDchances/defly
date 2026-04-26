<?php

namespace App\Filament\Resources\Defenders;

use App\Filament\Resources\Defenders\Pages\CreateDefender;
use App\Filament\Resources\Defenders\Pages\EditDefender;
use App\Filament\Resources\Defenders\Pages\ListDefenders;
use App\Filament\Resources\Defenders\RelationManagers\DecisionsRelationManager;
use App\Filament\Resources\Defenders\RelationManagers\PoliciesRelationManager;
use App\Filament\Resources\Defenders\Schemas\DefenderForm;
use App\Filament\Resources\Defenders\Tables\DefendersTable;
use App\Models\Defender;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DefenderResource extends Resource
{
    protected static ?string $model = Defender::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

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
            PoliciesRelationManager::class,
            DecisionsRelationManager::class,
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

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigations.groups.management');
    }

    public static function getModelLabel(): string
    {
        return __('models.defender.name');
    }
}

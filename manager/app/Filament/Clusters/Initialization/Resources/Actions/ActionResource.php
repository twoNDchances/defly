<?php

namespace App\Filament\Clusters\Initialization\Resources\Actions;

use App\Filament\Clusters\Initialization\InitializationCluster;
use App\Filament\Clusters\Initialization\Resources\Actions\Pages\CreateAction;
use App\Filament\Clusters\Initialization\Resources\Actions\Pages\EditAction;
use App\Filament\Clusters\Initialization\Resources\Actions\Pages\ListActions;
use App\Filament\Clusters\Initialization\Resources\Actions\Schemas\ActionForm;
use App\Filament\Clusters\Initialization\Resources\Actions\Tables\ActionsTable;
use App\Models\Action;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActionResource extends Resource
{
    protected static ?string $model = Action::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    protected static ?string $cluster = InitializationCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ActionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActionsTable::configure($table);
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
            'index' => ListActions::route('/'),
            'create' => CreateAction::route('/create'),
            'edit' => EditAction::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('models.action.name');
    }
}

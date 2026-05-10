<?php

namespace App\Filament\Resources\Labels\RelationManagers;

use App\Filament\Components\Action\ActionForm;
use App\Filament\Components\Action\ActionTable;
use App\Traits\Filament\Specifics\Action\ActionButton;
use App\Traits\Filament\Specifics\Action\ActionData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ActionsRelationManager extends RelationManager
{
    use ActionButton, ActionData;

    protected static string $relationship = 'actions';

    public function form(Schema $schema): Schema
    {
        return $schema->components(ActionForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(ActionTable::build())
            ->filters([
                //
            ])
            ->headerActions([
                self::attachButton(),
            ])
            ->recordActions([
                self::buttonGroup(delete: false, more: [self::detachButton()]),
            ])
            ->toolbarActions([
                self::bulkButtonGroup(false, [self::detachBulkButton()]),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('models.action.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.action.name');
    }
}

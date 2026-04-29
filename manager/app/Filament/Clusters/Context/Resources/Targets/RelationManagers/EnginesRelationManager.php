<?php

namespace App\Filament\Clusters\Context\Resources\Targets\RelationManagers;

use App\Filament\Components\Engine\EngineForm;
use App\Filament\Components\Engine\EngineTable;
use App\Traits\Filament\Specifics\Engine\EngineButton;
use App\Traits\Filament\Specifics\Engine\EngineData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EnginesRelationManager extends RelationManager
{
    use EngineButton, EngineData;

    protected static string $relationship = 'engines';

    public function form(Schema $schema): Schema
    {
        return $schema->components(EngineForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(EngineTable::build())
            ->filters([
                //
            ])
            ->headerActions([
                self::attachAndLockButton(),
            ])
            ->recordActions([
                self::buttonGroup(edit: false, delete: false, more: [self::detachAndUnlockButton()]),
            ])
            ->toolbarActions([
                self::bulkButtonGroup(false, [self::detachAndUnlockBulkButton()]),
            ])
            ->reorderable('order');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('models.engine.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.engine.name');
    }
}

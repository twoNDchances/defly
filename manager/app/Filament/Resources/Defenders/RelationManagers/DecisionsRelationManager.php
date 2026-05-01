<?php

namespace App\Filament\Resources\Defenders\RelationManagers;

use App\Filament\Components\Decision\DecisionForm;
use App\Filament\Components\Decision\DecisionTable;
use App\Traits\Filament\Specifics\Decision\DecisionButton;
use App\Traits\Filament\Specifics\Decision\DecisionData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DecisionsRelationManager extends RelationManager
{
    use DecisionButton, DecisionData;

    protected static string $relationship = 'decisions';

    public function form(Schema $schema): Schema
    {
        return $schema->components(DecisionForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([DecisionTable::getIsImplemented(), ...DecisionTable::build()])
            ->filters([
                //
            ])
            ->headerActions([
                self::attachDecisionsAndLockButton(),
            ])
            ->recordActions([
                self::buttonGroup(
                    edit: false,
                    delete: false,
                    more: [
                        self::implementDecisionButton($this->getOwnerRecord()),
                        self::suspendDecisionButton($this->getOwnerRecord()),
                        self::detachDecisionsAndUnlockButton(),
                    ],
                ),
            ])
            ->toolbarActions([
                self::bulkButtonGroup(
                    false,
                    [
                        self::implementDecisionBulkButton($this->getOwnerRecord()),
                        self::suspendDecisionBulkButton($this->getOwnerRecord()),
                        self::detachDecisionsAndUnlockBulkButton(),
                    ],
                ),
            ])
            ->reorderable('order')
            ->poll('5s');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('models.decision.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.decision.name');
    }
}

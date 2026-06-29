<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Defenders\RelationManagers;

use App\Enums\Principle\ValidationStatus;
use App\Filament\Components\Principle\PrincipleForm;
use App\Filament\Components\Principle\PrincipleTable;
use App\Traits\Filament\Specifics\Principle\PrincipleButton;
use App\Traits\Filament\Specifics\Principle\PrincipleData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PrinciplesRelationManager extends RelationManager
{
    use PrincipleButton, PrincipleData;

    protected static string $relationship = 'principles';

    public function form(Schema $schema): Schema
    {
        return $schema->components(PrincipleForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn ($query) => $query->where('validation_status', ValidationStatus::Passed))
            ->columns([PrincipleTable::getIsApplied(), ...PrincipleTable::build()])
            ->filters([
                //
            ])
            ->headerActions([
                self::attachPrinciplesAndLockButton()->recordSelectOptionsQuery(fn ($query) => $query->where('validation_status', ValidationStatus::Passed)),
            ])
            ->recordActions([
                self::buttonGroup(
                    edit: false,
                    delete: false,
                    more: [
                        self::applyPrincipleButton($this->getOwnerRecord()),
                        self::revokePrincipleButton($this->getOwnerRecord()),
                        self::detachPrinciplesAndUnlockButton(),
                    ],
                ),
            ])
            ->toolbarActions([
                self::bulkButtonGroup(
                    false,
                    [
                        self::applyPrincipleBulkButton($this->getOwnerRecord()),
                        self::revokePrincipleBulkButton($this->getOwnerRecord()),
                        self::detachPrinciplesAndUnlockBulkButton(),
                    ],
                ),
            ])
            ->reorderable('order')
            ->poll('5s');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('models.principle.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.principle.name');
    }
}

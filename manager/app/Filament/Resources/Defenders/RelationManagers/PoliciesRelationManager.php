<?php

namespace App\Filament\Resources\Defenders\RelationManagers;

use App\Enums\Policy\ValidationStatus;
use App\Filament\Components\Policy\PolicyForm;
use App\Filament\Components\Policy\PolicyTable;
use App\Traits\Filament\Specifics\Policy\PolicyButton;
use App\Traits\Filament\Specifics\Policy\PolicyData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PoliciesRelationManager extends RelationManager
{
    use PolicyButton, PolicyData;

    protected static string $relationship = 'policies';

    public function form(Schema $schema): Schema
    {
        return $schema->components(PolicyForm::build());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn ($query) => $query->where('validation_status', ValidationStatus::Passed))
            ->columns(PolicyTable::build())
            ->filters([
                //
            ])
            ->headerActions([
                self::attachAndLockButton()->recordSelectOptionsQuery(fn ($query) => $query->where('validation_status', ValidationStatus::Passed)),
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
        return __('models.policy.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.policy.name');
    }
}

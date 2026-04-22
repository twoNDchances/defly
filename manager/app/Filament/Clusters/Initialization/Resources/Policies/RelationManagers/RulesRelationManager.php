<?php

namespace App\Filament\Clusters\Initialization\Resources\Policies\RelationManagers;

use App\Filament\Components\Rule\RuleForm;
use App\Filament\Components\Rule\RuleTable;
use App\Services\Lock;
use App\Traits\Filament\Specifics\Rule\RuleButton;
use App\Traits\Filament\Specifics\Rule\RuleData;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RulesRelationManager extends RelationManager
{
    use RuleButton, RuleData;

    protected static string $relationship = 'rules';

    public function form(Schema $schema): Schema
    {
        return $schema->components(RuleForm::build());
    }

    public function table(Table $table): Table
    {
        $policyPhase = $this->getOwnerRecord()->getRawOriginal('phase');

        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn ($query) => $query->where('phase', $policyPhase))
            ->columns(RuleTable::build())
            ->filters([
                //
            ])
            ->headerActions([
                self::createButton()->after(fn (Model $record) => Lock::syncByRelationship($record::class, $record->getKey())),
                self::attachAndLockButton()->recordSelectOptionsQuery(fn ($query) => $query->where('phase', $policyPhase)),
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
        return __('models.rule.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.rule.name');
    }
}

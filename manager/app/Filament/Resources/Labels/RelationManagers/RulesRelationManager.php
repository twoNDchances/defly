<?php

namespace App\Filament\Resources\Labels\RelationManagers;

use App\Filament\Components\Rule\RuleForm;
use App\Filament\Components\Rule\RuleTable;
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
        return $table
            ->recordTitleAttribute('name')
            ->columns(RuleTable::build())
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
        return __('models.rule.name');
    }

    public static function getRecordLabel(): ?string
    {
        return __('models.rule.name');
    }
}

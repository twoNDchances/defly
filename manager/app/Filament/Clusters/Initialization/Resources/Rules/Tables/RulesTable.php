<?php

namespace App\Filament\Clusters\Initialization\Resources\Rules\Tables;

use App\Filament\Components\Rule\RuleTable;
use Filament\Tables\Table;

class RulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(RuleTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                RuleTable::buttonGroup(more: [RuleTable::cloneButton()]),
            ])
            ->toolbarActions([
                RuleTable::bulkButtonGroup(false, [RuleTable::deleteUnlockedBulkButton()]),
            ]);
    }
}

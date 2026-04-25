<?php

namespace App\Filament\Clusters\Initialization\Resources\Decisions\Tables;

use App\Filament\Components\Decision\DecisionTable;
use Filament\Tables\Table;

class DecisionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(DecisionTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                DecisionTable::buttonGroup(more: [DecisionTable::cloneButton()]),
            ])
            ->toolbarActions([
                DecisionTable::bulkButtonGroup(false, [DecisionTable::deleteUnlockedBulkButton()]),
            ]);
    }
}

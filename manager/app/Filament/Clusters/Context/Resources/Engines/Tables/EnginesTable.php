<?php

namespace App\Filament\Clusters\Context\Resources\Engines\Tables;

use App\Filament\Components\Engine\EngineTable;
use Filament\Tables\Table;

class EnginesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(EngineTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                EngineTable::buttonGroup(more: [EngineTable::cloneButton()]),
            ])
            ->toolbarActions([
                EngineTable::bulkButtonGroup(false, [EngineTable::deleteUnlockedBulkButton()]),
            ]);
    }
}

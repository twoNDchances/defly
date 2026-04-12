<?php

namespace App\Filament\Clusters\Context\Resources\Targets\Tables;

use App\Filament\Components\Target\TargetTable;
use Filament\Tables\Table;

class TargetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(TargetTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                TargetTable::buttonGroup(),
            ])
            ->toolbarActions([
                TargetTable::bulkButtonGroup(),
            ]);
    }
}

<?php

namespace App\Filament\Clusters\Infrastructure\Resources\Guards\Tables;

use App\Filament\Components\Guard\GuardTable;
use Filament\Tables\Table;

class GuardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(GuardTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                GuardTable::buttonGroup(),
            ])
            ->toolbarActions([
                GuardTable::bulkButtonGroup(),
            ]);
    }
}

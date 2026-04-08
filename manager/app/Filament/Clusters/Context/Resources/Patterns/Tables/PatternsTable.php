<?php

namespace App\Filament\Clusters\Context\Resources\Patterns\Tables;

use App\Filament\Components\Pattern\PatternTable;
use Filament\Tables\Table;

class PatternsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(PatternTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                PatternTable::viewButton(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Labels\Tables;

use App\Filament\Components\Label\LabelTable;
use Filament\Tables\Table;

class LabelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(LabelTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                LabelTable::buttonGroup(),
            ])
            ->toolbarActions([
                LabelTable::bulkButtonGroup(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Defenders\Tables;

use App\Filament\Components\Defender\DefenderTable;
use Filament\Tables\Table;

class DefendersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(DefenderTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                DefenderTable::buttonGroup(),
            ])
            ->toolbarActions([
                DefenderTable::bulkButtonGroup(),
            ])
            ->recordActions([
                DefenderTable::buttonGroup(more: [DefenderTable::deployDefenderButton(), DefenderTable::cancelDefenderButton()]),
            ])
            ->toolbarActions([
                DefenderTable::bulkButtonGroup(
                    false,
                    [
                        DefenderTable::deployDefenderBulkButton(),
                        DefenderTable::cancelDefenderBulkButton(),
                        DefenderTable::deleteDoneBulkButton(),
                    ],
                ),
            ])
            ->poll('5s');
    }
}

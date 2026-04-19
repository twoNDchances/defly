<?php

namespace App\Filament\Clusters\Initialization\Resources\Actions\Tables;

use App\Filament\Components\Action\ActionTable;
use Filament\Tables\Table;

class ActionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(ActionTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                ActionTable::buttonGroup(more: [ActionTable::cloneButton()])
            ])
            ->toolbarActions([
                ActionTable::bulkButtonGroup(false, [ActionTable::deleteUnlockedBulkButton()])
            ]);
    }
}

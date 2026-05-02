<?php

namespace App\Filament\Clusters\Authentication\Resources\Keys\Tables;

use App\Filament\Components\Key\KeyTable;
use Filament\Tables\Table;

class KeysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(KeyTable::build())
            ->modifyQueryUsing(fn ($query) => $query->onlyOwner())
            ->filters([
                //
            ])
            ->recordActions([
                KeyTable::buttonGroup(),
            ])
            ->toolbarActions([
                KeyTable::bulkButtonGroup(),
            ]);
    }
}

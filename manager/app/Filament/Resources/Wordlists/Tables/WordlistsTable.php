<?php

namespace App\Filament\Resources\Wordlists\Tables;

use App\Filament\Components\Wordlist\WordlistTable;
use Filament\Tables\Table;

class WordlistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(WordlistTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                WordlistTable::buttonGroup(more: [WordlistTable::cloneButton()]),
            ])
            ->toolbarActions([
                WordlistTable::bulkButtonGroup(false, [WordlistTable::deleteUnlockedBulkButton()]),
            ]);
    }
}

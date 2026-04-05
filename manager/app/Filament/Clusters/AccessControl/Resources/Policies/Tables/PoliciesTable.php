<?php

namespace App\Filament\Clusters\AccessControl\Resources\Policies\Tables;

use App\Filament\Components\Policy\PolicyTable;
use Filament\Tables\Table;

class PoliciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(PolicyTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                PolicyTable::buttonGroup(),
            ])
            ->toolbarActions([
                PolicyTable::bulkButtonGroup(),
            ]);
    }
}

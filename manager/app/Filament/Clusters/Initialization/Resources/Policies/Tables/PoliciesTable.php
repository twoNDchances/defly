<?php

namespace App\Filament\Clusters\Initialization\Resources\Policies\Tables;

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
                PolicyTable::buttonGroup(
                    more: [
                        PolicyTable::validateButton(),
                        PolicyTable::cloneButton(),
                    ],
                ),
            ])
            ->toolbarActions([
                PolicyTable::bulkButtonGroup(
                    false,
                    [
                        PolicyTable::validateBulkButton(),
                        PolicyTable::deleteUnlockedBulkButton(),
                    ],
                ),
            ])
            ->poll('5s');
    }
}

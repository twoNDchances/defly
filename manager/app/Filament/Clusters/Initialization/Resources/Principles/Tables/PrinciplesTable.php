<?php

namespace App\Filament\Clusters\Initialization\Resources\Principles\Tables;

use App\Filament\Components\Principle\PrincipleTable;
use Filament\Tables\Table;

class PrinciplesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(PrincipleTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                PrincipleTable::buttonGroup(
                    more: [
                        PrincipleTable::validatePrincipleButton(),
                        PrincipleTable::clonePrincipleButton(),
                    ],
                ),
            ])
            ->toolbarActions([
                PrincipleTable::bulkButtonGroup(
                    false,
                    [
                        PrincipleTable::validatePrincipleBulkButton(),
                        PrincipleTable::deleteUnlockedBulkButton(),
                    ],
                ),
            ])
            ->poll('5s');
    }
}

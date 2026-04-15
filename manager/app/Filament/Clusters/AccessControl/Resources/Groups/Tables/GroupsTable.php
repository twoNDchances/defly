<?php

namespace App\Filament\Clusters\AccessControl\Resources\Groups\Tables;

use App\Filament\Components\Group\GroupTable;
use Filament\Tables\Table;

class GroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(GroupTable::build())
            ->filters([
                //
            ])
            ->recordActions([
                GroupTable::buttonGroup(),
            ])
            ->toolbarActions([
                GroupTable::bulkButtonGroup(),
            ]);
    }
}

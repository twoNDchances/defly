<?php

namespace App\Filament\Clusters\Authentication\Resources\Users\Tables;

use App\Filament\Components\User\UserTable;
use App\Services\Identification;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(UserTable::build())
            ->modifyQueryUsing(fn ($query) => match (Identification::isRoot()) {
                true => $query->excludeCurrent(),
                false => $query->excludeCurrent()->excludeRoot(),
            })
            ->filters([
                //
            ])
            ->recordActions([
                UserTable::buttonGroup(),
            ])
            ->toolbarActions([
                UserTable::bulkButtonGroup(false, [UserTable::deleteMultiUserButton()]),
            ]);
    }
}

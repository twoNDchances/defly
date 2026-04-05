<?php

namespace App\Filament\Clusters\Authentication\Resources\Users\Tables;

use App\Filament\Components\User\UserTable;
use App\Models\User;
use App\Services\Identification;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(UserTable::build())
            ->query(function () {
                $query = User::query()->excludeCurrent();

                return Identification::isRoot() ? $query : $query->excludeRoot();
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

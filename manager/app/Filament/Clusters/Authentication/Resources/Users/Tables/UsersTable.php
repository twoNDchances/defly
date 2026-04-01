<?php

namespace App\Filament\Clusters\Authentication\Resources\Users\Tables;

use App\Models\User;
use App\Services\Identification;
use App\Services\Security;
use App\Traits\Filament\Buttons\UserButton;
use App\Traits\Filament\Columns\UserColumn;
use Filament\Tables\Table;

class UsersTable
{
    use UserButton;
    use UserColumn;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                self::email(),
                self::isVerified(),
                self::isRoot(),
                self::isActivated(),
                self::permissions(),
                self::policies(),
                self::createdBy(),
                self::createdAt(),
                self::updatedAt(),
            ])
            ->query(function () {
                $users = User::where('email', '!=', Identification::getEmail());
                if (Identification::isRoot()) {
                    return $users;
                }

                return Security::viewAnyOther($users->where('is_root', false));
            })
            ->filters([
                //
            ])
            ->recordActions([
                self::buttonGroup(),
            ])
            ->toolbarActions([
                self::bulkButtonGroup(false, [self::deleteBulkButton()]),
            ]);
    }
}

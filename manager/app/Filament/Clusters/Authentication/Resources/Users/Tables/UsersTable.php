<?php

namespace App\Filament\Clusters\Authentication\Resources\Users\Tables;

use App\Models\User;
use App\Services\Identification;
use App\Traits\Filament\Specifics\User\UserButton;
use App\Traits\Filament\Specifics\User\UserColumn;
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
                self::canManageFromOther(),
                self::createdBy(),
                self::createdAt(),
                self::updatedAt(),
            ])
            ->query(function () {
                $users = User::query()->manage()->where('email', '!=', Identification::getEmail());
                if (Identification::isRoot()) {
                    return $users;
                }

                return $users->where('is_root', false);
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

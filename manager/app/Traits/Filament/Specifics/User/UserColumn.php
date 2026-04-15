<?php

namespace App\Traits\Filament\Specifics\User;

use App\Services\Identification;
use App\Traits\Filament\Generals\Components\Column;

trait UserColumn
{
    use Column, UserButton, UserData;

    public static function getEmail()
    {
        return self::textColumn('email', __('models.user.fields.email'))
            ->description(fn ($record) => $record->name);
    }

    public static function getIsVerified()
    {
        return self::booleanColumn('is_verified', __('models.user.fields.is_verified'));
    }

    public static function getIsRoot()
    {
        $condition = Identification::isRoot();

        return self::booleanColumn('is_root', __('models.user.fields.is_root'))
            ->disabled(! $condition)
            ->visible($condition);
    }

    public static function getIsActivated()
    {
        return self::booleanColumn('is_activated', __('models.user.fields.is_activated'));
    }

    public static function getPermissions()
    {
        return self::relationshipColumn('permissions.name', __('tables.user.permissions'));
    }

    public static function getGroups()
    {
        return self::relationshipColumn('groups.name', __('tables.user.groups'));
    }
}

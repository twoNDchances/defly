<?php

namespace App\Traits\Filament\Specifics\User;

use App\Services\Identification;
use App\Traits\Filament\Generals\Components\Column;

trait UserColumn
{
    use Column;

    public static function email()
    {
        return self::textColumn('email', __('tables.columns.user.email').' & '.__('tables.columns.user.name'))
            ->description(fn ($record) => $record->name);
    }

    public static function isVerified()
    {
        return self::booleanColumn('is_verified', __('tables.columns.user.is_verified'));
    }

    public static function isRoot()
    {
        $condition = Identification::isRoot();

        return self::booleanColumn('is_root', __('tables.columns.user.is_root'))
            ->disabled(! $condition)
            ->visible($condition);
    }

    public static function isActivated()
    {
        return self::booleanColumn('is_activated', __('tables.columns.user.is_activated'));
    }

    public static function permissions()
    {
        return self::relationshipColumn('permissions.name', __('tables.columns.user.permissions'));
    }

    public static function policies()
    {
        return self::relationshipColumn('policies.name', __('tables.columns.user.policies'));
    }
}

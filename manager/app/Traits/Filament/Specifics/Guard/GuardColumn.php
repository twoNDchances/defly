<?php

namespace App\Traits\Filament\Specifics\Guard;

use App\Traits\Filament\Generals\Components\Column;

trait GuardColumn
{
    use Column, GuardButton, GuardData;

    public static function getName()
    {
        return self::textColumn('name', __('models.guard.fields.name'));
    }

    public static function getExpiredAt()
    {
        return self::datetimeColumn('expired_at', __('models.guard.fields.expired_at'));
    }

    public static function getDefenders()
    {
        return self::relationshipColumn('defenders.name', __('tables.guard.defenders'));
    }

    public static function getUsers()
    {
        return self::relationshipColumn('users.email', __('tables.guard.users'));
    }
}

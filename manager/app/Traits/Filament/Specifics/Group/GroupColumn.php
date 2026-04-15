<?php

namespace App\Traits\Filament\Specifics\Group;

use App\Traits\Filament\Generals\Components\Column;

trait GroupColumn
{
    use Column, GroupButton, GroupData;

    public static function getName()
    {
        return self::textColumn('name', __('models.group.fields.name'));
    }

    public static function getUsers()
    {
        return self::relationshipColumn('users.email', __('tables.group.users'));
    }

    public static function getPermissions()
    {
        return self::relationshipColumn('permissions.name', __('tables.group.permissions'));
    }
}

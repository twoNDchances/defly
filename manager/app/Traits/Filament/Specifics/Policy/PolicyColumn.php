<?php

namespace App\Traits\Filament\Specifics\Policy;

use App\Traits\Filament\Generals\Components\Column;

trait PolicyColumn
{
    use Column, PolicyButton, PolicyData;

    public static function getName()
    {
        return self::textColumn('name', __('models.policy.fields.name'));
    }

    public static function getUsers()
    {
        return self::relationshipColumn('users.email', __('tables.policy.users'));
    }

    public static function getPermissions()
    {
        return self::relationshipColumn('permissions.name', __('tables.policy.permissions'));
    }
}

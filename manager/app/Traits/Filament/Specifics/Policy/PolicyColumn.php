<?php

namespace App\Traits\Filament\Specifics\Policy;

use App\Traits\Filament\Generals\Components\Column;

trait PolicyColumn
{
    use Column;
    use PolicyButton;

    public static function name()
    {
        return self::textColumn('name', __('tables.columns.policy.name'));
    }

    public static function users()
    {
        return self::relationshipColumn('users.email', __('tables.columns.policy.users'));
    }

    public static function permissions()
    {
        return self::relationshipColumn('permissions.name', __('tables.columns.policy.permissions'));
    }
}

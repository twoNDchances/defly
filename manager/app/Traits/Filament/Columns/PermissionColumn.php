<?php

namespace App\Traits\Filament\Columns;

use App\Services\Security;
use App\Traits\Filament\Column;

trait PermissionColumn
{
    use Column;

    public static function name()
    {
        return self::textColumn('name', __('tables.columns.permission.name'));
    }

    public static function appliedFor()
    {
        return self::textColumn('applied_for', __('tables.columns.permission.applied_for'));
    }

    public static function action()
    {
        return self::textColumn('action', __('tables.columns.permission.action'))
        ->getStateUsing(fn ($record) => Security::generatePermissionList(true)[$record->applied_for][$record->action]);
    }
}

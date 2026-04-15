<?php

namespace App\Filament\Components\Policy;

use App\Traits\Filament\Specifics\Policy\PolicyColumn;

class PolicyTable
{
    use PolicyColumn;

    public static function build()
    {
        return [
            self::getName(),
            self::getUsers(),
            self::getPermissions(),
            self::getLabels(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

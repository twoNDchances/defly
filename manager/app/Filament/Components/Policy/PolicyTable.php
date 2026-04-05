<?php

namespace App\Filament\Components\Policy;

use App\Traits\Filament\Specifics\Policy\PolicyColumn;

class PolicyTable
{
    use PolicyColumn;

    public static function build()
    {
        return [
            self::name(),
            self::users(),
            self::permissions(),
            self::labels(),
            self::createdBy(),
            self::createdAt(),
            self::updatedAt(),
        ];
    }
}

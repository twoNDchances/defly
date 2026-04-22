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
            self::getLevel(),
            self::getPhase(),
            self::getValidationStatus(),
            self::getRules(),
            self::getLabels(),
            self::getLocked(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

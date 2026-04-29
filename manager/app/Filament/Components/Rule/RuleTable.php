<?php

namespace App\Filament\Components\Rule;

use App\Traits\Filament\Specifics\Rule\RuleColumn;

class RuleTable
{
    use RuleColumn;

    public static function build()
    {
        return [
            self::getName(),
            self::getPhase(),
            self::getTarget(),
            self::getComparator(),
            self::getWordlist(),
            self::getActions(),
            self::getPrinciples(),
            self::getLabels(),
            self::getIsLocked(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

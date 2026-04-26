<?php

namespace App\Filament\Components\Defender;

use App\Traits\Filament\Specifics\Defender\DefenderColumn;

class DefenderTable
{
    use DefenderColumn;

    public static function build()
    {
        return [
            self::getName(),
            self::getStatus(),
            self::getDeploymentStatus(),
            self::getPolicies(),
            self::getDecisions(),
            self::getLabels(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

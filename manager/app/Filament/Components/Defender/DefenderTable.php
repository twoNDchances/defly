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
            self::getProxyPort(),
            self::getStatus(),
            self::getDeploymentStatus(),
            self::getPrinciples(),
            self::getDecisions(),
            self::getLabels(),
            self::getCreatedBy(),
            self::getCreatedAt(),
            self::getUpdatedAt(),
        ];
    }
}

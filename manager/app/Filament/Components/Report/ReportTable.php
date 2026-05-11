<?php

namespace App\Filament\Components\Report;

use App\Traits\Filament\Specifics\Report\ReportColumn;

class ReportTable
{
    use ReportColumn;

    public static function build()
    {
        return [
            self::getIsReviewed(),
            self::getCreatedAt(),
            self::getTriggeredBy(),
            self::getCreatedBy(),
        ];
    }
}

<?php

namespace App\Filament\Components\Report;

use App\Traits\Filament\Specifics\Report\ReportColumn;

class ReportTable
{
    use ReportColumn;

    public static function build()
    {
        return [
            self::getCreatedAt(),
            self::getTriggeredBy(),
            self::getCreatedBy(),
        ];
    }
}

<?php

namespace App\Traits\Filament\Specifics\Report;

use App\Traits\Filament\Specifics\GeneralData;

trait ReportData
{
    use GeneralData { GeneralData::methodOptionsAndColors as limitedMethodOptionsAndColors; }

    public static function methodOptionsAndColors()
    {
        return [
            'options' => [
                ...self::limitedMethodOptionsAndColors()['options'],
                'head' => 'HEAD',
                'options' => 'OPTIONS',
            ],
            'colors' => [
                ...self::limitedMethodOptionsAndColors()['colors'],
                'head' => 'teal',
                'options' => 'pink',
            ],
        ];
    }
}

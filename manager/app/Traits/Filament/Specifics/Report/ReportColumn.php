<?php

namespace App\Traits\Filament\Specifics\Report;

use App\Traits\Filament\Generals\Components\Column;

trait ReportColumn
{
    use Column, ReportButton, ReportData;

    public static function getCreatedAt()
    {
        return self::datetimeColumn('created_at', __('models.generals.bases.created_at'));
    }

    public static function getTriggeredBy()
    {
        return self::textColumn('triggeredBy.name', __('models.report.fields.triggered_by'));
    }

    public static function getCreatedBy()
    {
        return self::textColumn('createdBy.name', __('models.generals.bases.created_by'))
            ->badge();
    }

    public static function getIsReviewed()
    {
        return self::booleanColumn('is_reviewed', __('models.report.fields.is_reviewed'));
    }
}

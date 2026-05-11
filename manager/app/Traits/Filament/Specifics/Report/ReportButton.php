<?php

namespace App\Traits\Filament\Specifics\Report;

use App\Services\Logger;
use App\Traits\Filament\Generals\Components\Button;
use Filament\Support\Icons\Heroicon;

trait ReportButton
{
    use Button;

    public static function reviewReportButton()
    {
        return self::button(
            'review_button',
            __('tables.report.buttons.review'),
            Heroicon::OutlinedCheckCircle,
            function ($record) {
                if ($record->is_reviewed) {
                    return;
                }

                $record->forceFill([
                    'is_reviewed' => true,
                ])->save();

                Logger::log($record, 'review');
            },
        )
            ->authorize('review')
            ->color('success')
            ->visible(fn ($record) => ! $record->is_reviewed);
    }

    public static function reviewReportBulkButton()
    {
        return self::bulkButton(
            'review_bulk_button',
            __('tables.report.buttons.reviewAny'),
            Heroicon::OutlinedCheckCircle,
            function ($records) {
                foreach ($records as $record) {
                    if ($record->is_reviewed) {
                        continue;
                    }

                    $record->forceFill([
                        'is_reviewed' => true,
                    ])->save();

                    Logger::log($record, 'review');
                }
            },
        )
            ->authorize('reviewAny')
            ->color('success')
            ->deselectRecordsAfterCompletion();
    }
}

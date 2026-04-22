<?php

namespace App\Traits\Filament\Specifics\Policy;

use App\Enums\Policy\ValidationStatus;
use App\Jobs\PolicyValidation;
use App\Traits\Filament\Generals\Components\Button;
use Filament\Support\Icons\Heroicon;

trait PolicyButton
{
    use Button;

    public static function validateButton()
    {
        return self::button(
            'validate_button',
            __('tables.policy.buttons.validate_button'),
            Heroicon::OutlinedCheck,
            function ($record) {
                $record->validation_status = ValidationStatus::Pending;
                $record->save();
                PolicyValidation::dispatch($record->id);
            },
        )
        ->authorize('validate')
        ->color('cyan');
    }

    public static function validateBulkButton()
    {
        return self::bulkButton(
            'validate_bulk_button',
            __('tables.policy.buttons.validate_bulk_button'),
            Heroicon::OutlinedCheck,
            function ($records) {
                foreach ($records as $record) {
                    $record->validation_status = ValidationStatus::Pending;
                    $record->save();
                    PolicyValidation::dispatch($record->id);
                }
            }
        )
        ->authorize('validateAny')
        ->color('cyan');
    }

    public static function deleteUnlockedBulkButton()
    {
        return self::deleteBulkButton()
            ->action(function ($records) {
                foreach ($records as $record) {
                    if ($record->hasAttribute('is_locked') && $record->is_locked == true) {
                        continue;
                    }

                    if (in_array($record->validation_status, [
                        ValidationStatus::Pending,
                        ValidationStatus::Validating,
                    ], true)) {
                        continue;
                    }

                    $record->delete();
                }
            });
    }
}

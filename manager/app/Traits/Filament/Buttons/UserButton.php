<?php

namespace App\Traits\Filament\Buttons;

use App\Services\Identification;
use App\Traits\Filament\Button;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

trait UserButton
{
    use Button;

    public static function generatePasswordButton()
    {
        return self::button(
            'generate_password_button',
            __('forms.user.buttons.generate_password'),
            Heroicon::OutlinedArrowPath,
            fn ($set) => $set('password', Str::random(16))
        );
    }

    public static function deleteBulkButton()
    {
        return self::bulkButton(
            'delete_bulk_button',
            __('actions.commons.delete_bulk'),
            Heroicon::OutlinedTrash,
            function ($records) {
                $count = 0;
                foreach ($records as $record) {
                    if ($record->id == Identification::getId()) {
                        continue;
                    }
                    if ($record->is_important && ! Identification::isRoot()) {
                        continue;
                    }
                    $record->delete();
                    $count++;
                }
            },
        )
            ->deselectRecordsAfterCompletion()
            ->requiresConfirmation()
            ->color('danger');
    }
}

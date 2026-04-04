<?php

namespace App\Traits\Filament\Specifics\User;

use App\Models\User;
use App\Services\Identification;
use App\Services\Security;
use App\Traits\Filament\Generals\Components\Button;
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
                    if ($record->is_root && ! Identification::isRoot()) {
                        continue;
                    }
                    if (! Security::checkPermission(Identification::getCurrent(), User::class, 'access_other')) {
                        if ($record->created_by != Identification::getId()) {
                            continue;
                        }
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

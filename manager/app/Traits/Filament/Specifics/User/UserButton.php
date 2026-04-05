<?php

namespace App\Traits\Filament\Specifics\User;

use App\Services\Identification;
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

    public static function deleteMultiUserButton()
    {
        return self::deleteBulkButton()
            ->action(function ($records) {
                foreach ($records as $record) {
                    if ($record->id == Identification::getId()) {
                        continue;
                    }
                    if ($record->is_root && ! Identification::isRoot()) {
                        continue;
                    }
                    $record->delete();
                }
            });
    }

    public static function detachMultiUserButton()
    {
        return self::detachBulkButton()
            ->action(function ($records, $livewire) {
                foreach ($records as $record) {
                    if ($record->id == Identification::getId()) {
                        continue;
                    }
                    if ($record->is_root && ! Identification::isRoot()) {
                        continue;
                    }
                    $permission = $livewire->getOwnerRecord();
                    $permission->users()->detach($record->id);
                }
            });
    }
}

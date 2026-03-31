<?php

namespace App\Filament\Components\User;

use App\Filament\Components\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

trait UserAction
{
    use Action;

    protected static function generatePassword()
    {
        return self::action(
            'generate_password',
            __('forms.user.actions.generate_password'),
            Heroicon::OutlinedArrowPath,
            fn ($set) => $set('password', Str::random(16))
        );
    }
}

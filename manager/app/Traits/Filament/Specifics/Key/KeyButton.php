<?php

namespace App\Traits\Filament\Specifics\Key;

use App\Traits\Filament\Generals\Components\Button;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

trait KeyButton
{
    use Button;

    public static function generateTokenButton()
    {
        return self::button(
            'generate_token_button',
            __('forms.key.buttons.generate_token'),
            Heroicon::OutlinedArrowPath,
            fn ($set) => $set('token', Str::random(64)),
        );
    }
}
